import type {Page, Response} from '@playwright/test';
import {Admin, expect, RequestUtils, test} from '@wordpress/e2e-test-utils-playwright';
import {addFilter, chip, filterBar, listRows, listTable, popover, rowById, rowWithText} from './utils/list-table';
import {resetLayout} from './utils/user';

/**
 * The shared list filters: the filter bar, its chips, "Add filter", the date range, the labeled
 * search and the test-mode chip, on the screens that declare them.
 *
 * The unit tests cover the declarations, the URL rules and the request builder. What they cannot
 * cover is the whole loop in a browser: a click in the bar reaching the endpoint with the right
 * parameters, and the endpoint answering with the rows the admin expects.
 *
 * Nothing here hard-codes a count or a name from one site. Expected counts come from the v3 REST
 * API, which does not share the list endpoints' filter code, and the names searched for are taken
 * from records the site already has. A test whose records the site does not have is skipped and
 * says why. No test runs a bulk action or any action that opens a browser dialog.
 */
const DONATIONS = 'post_type=give_forms&page=give-payment-history';
const DONORS = 'post_type=give_forms&page=give-donors';
const CAMPAIGNS = 'post_type=give_forms&page=give-campaigns';
const SUBSCRIPTIONS = 'post_type=give_forms&page=give-subscriptions';

const DONATIONS_REQUEST = /give-api\/v2\/admin\/donations\?/;
const DONORS_REQUEST = /give-api\/v2\/admin\/donors\?/;
const CAMPAIGNS_REQUEST = /givewp\/v3\/campaigns\/list-table\?/;
const FORMS_REQUEST = /give-api\/v2\/admin\/forms\?/;
const OPTIONS_REQUEST = /list-table-filter-options\//;

type V3Donation = {
    id: number;
    createdAt: string;
    status: string;
    mode: string;
    donorId: number;
    firstName: string;
    lastName: string;
    email: string;
};
type V3Donor = {id: number; name: string};
type V3Campaign = {id: number; status: string};

/**
 * Every record of a v3 collection, page by page. The v3 donations route reports a wrong total, so
 * this reads until a page comes back short.
 */
async function allRecords<T>(requestUtils: RequestUtils, path: string, params: Record<string, unknown>): Promise<T[]> {
    const records: T[] = [];

    for (let page = 1; page <= 50; page++) {
        const batch = (await requestUtils.rest({path, params: {...params, per_page: 100, page}})) as T[];

        records.push(...batch);

        if (batch.length < 100) {
            break;
        }
    }

    return records;
}

/**
 * The list request the page sends next whose URL passes `matches`.
 */
function nextListRequest(page: Page, endpoint: RegExp, matches: (url: URL) => boolean = () => true): Promise<Response> {
    return page.waitForResponse((response) => {
        const url = new URL(response.url());

        return endpoint.test(response.url()) && matches(url);
    });
}

async function visit(page: Page, admin: Admin, query: string): Promise<void> {
    await admin.visitAdminPage('edit.php', query);
    await expect(listTable(page).locator('.dataviews-view-table, .dataviews-no-results').first()).toBeVisible({
        timeout: 20_000,
    });
}

/**
 * Turns the test-mode switch to `isOn` when it is not there yet, and waits for the list to reload.
 */
async function setTestMode(page: Page, isOn: boolean): Promise<void> {
    const toggle = page.getByRole('switch');

    if ((await toggle.getAttribute('aria-checked')) === String(isOn)) {
        return;
    }

    const reloaded = nextListRequest(
        page,
        DONATIONS_REQUEST,
        (url) => url.searchParams.get('testMode') === String(isOn)
    );
    await toggle.click();
    await reloaded;
}

/**
 * `YYYY-MM-DD` of a day in the month before today, in the browser's calendar.
 */
function dayOfLastMonth(day: number): string {
    const today = new Date();
    const date = new Date(today.getFullYear(), today.getMonth() - 1, day);

    return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(day).padStart(2, '0')].join('-');
}

test.describe('Shared list filters', () => {
    let dialogs: string[] = [];

    test.beforeEach(async ({page}) => {
        // No test opens a browser dialog; one that shows up is a bug, and it must not block the run.
        dialogs = [];
        page.on('dialog', (dialog) => {
            dialogs.push(dialog.message());
            void dialog.dismiss();
        });
    });

    test.afterEach(() => {
        expect(dialogs).toEqual([]);
    });

    test.describe('Donations', () => {
        test.beforeEach(async ({requestUtils}) => {
            await resetLayout(requestUtils, 'donations');
        });

        test('Status takes several values and the chip collapses past three', async ({page, admin}) => {
            await visit(page, admin, DONATIONS);
            await chip(page, 'Status').click();

            expect(await popover(page).getByRole('checkbox').count()).toBeGreaterThan(4);

            const fourNotTrash = popover(page)
                .locator('.givewp-options-filter-control__option')
                .filter({hasNotText: /^Trash$/});

            for (let index = 0; index < 4; index++) {
                const sent = nextListRequest(page, DONATIONS_REQUEST);
                await fourNotTrash.nth(index).getByRole('checkbox').check();
                await sent;
            }

            await page.keyboard.press('Escape');

            await expect(chip(page, 'Status')).toHaveText('Status: 4 selected');
            await expect(page).toHaveURL(/give-status=[a-z_-]+(,[a-z_-]+){3}(&|$)/);
        });

        test('Trash cannot be combined with another status', async ({page, admin}) => {
            await visit(page, admin, DONATIONS);
            await chip(page, 'Status').click();

            const first = popover(page)
                .locator('.givewp-options-filter-control__option')
                .filter({hasNotText: /^Trash$/})
                .first()
                .getByRole('checkbox');
            await first.check();

            const trashOnly = nextListRequest(
                page,
                DONATIONS_REQUEST,
                (url) => url.searchParams.get('status') === 'trash'
            );
            await popover(page).getByRole('checkbox', {name: 'Trash', exact: true}).check();
            await trashOnly;

            await expect(first).not.toBeChecked();
            await expect(popover(page).getByRole('checkbox', {checked: true})).toHaveCount(1);
        });

        test('"No campaign" cannot be combined with a campaign', async ({page, admin}) => {
            await visit(page, admin, DONATIONS);
            await chip(page, 'Campaign').click();

            const campaign = popover(page).getByRole('checkbox').nth(1);
            await expect(campaign).toBeVisible({timeout: 15_000});
            await campaign.check();

            const none = nextListRequest(
                page,
                DONATIONS_REQUEST,
                (url) => url.searchParams.get('campaignId') === 'none'
            );
            await popover(page).getByRole('checkbox', {name: 'No campaign', exact: true}).check();
            await none;

            await expect(campaign).not.toBeChecked();
            await expect(popover(page).getByRole('checkbox', {checked: true})).toHaveCount(1);
        });

        test('Donor is added from "Add filter" and found by name, with no email anywhere', async ({
            page,
            admin,
            requestUtils,
        }) => {
            const donors = await allRecords<V3Donor>(requestUtils, '/givewp/v3/donors', {});
            const donor = donors.find((record) => /^\p{L}{3,}/u.test(record.name ?? ''));
            test.skip(!donor, 'The site has no donor with a name to search for.');

            const urls: string[] = [];
            page.on('request', (request) => {
                if (DONATIONS_REQUEST.test(request.url()) || OPTIONS_REQUEST.test(request.url())) {
                    urls.push(request.url());
                }
            });

            await visit(page, admin, DONATIONS);
            await addFilter(page, 'Donor');

            const term = donor!.name.slice(0, 3);
            const searched = page.waitForResponse(
                (response) => OPTIONS_REQUEST.test(response.url()) && response.url().includes('search=')
            );
            await popover(page).getByRole('searchbox').or(popover(page).getByRole('textbox')).first().fill(term);
            const options = (await (await searched).json()) as {options: Array<{value: string; label: string}>};

            expect(options.options.every((option) => !option.label.includes('@'))).toBe(true);

            const filtered = nextListRequest(page, DONATIONS_REQUEST, (url) =>
                /^\d+$/.test(url.searchParams.get('donor') ?? '')
            );
            await popover(page).getByRole('radio').first().check();
            const donorId = new URL((await filtered).url()).searchParams.get('donor');

            await page.keyboard.press('Escape');

            await expect(page).toHaveURL(new RegExp(`give-donor=${donorId}(&|$)`));
            await expect(chip(page, 'Donor:')).toBeVisible();
            expect(urls.filter((url) => decodeURIComponent(url).includes('@'))).toEqual([]);
        });

        test('a date range from the 1st to the 15th of last month shows every donation of those days', async ({
            page,
            admin,
            requestUtils,
        }) => {
            const start = dayOfLastMonth(1);
            const end = dayOfLastMonth(15);
            const live = await allRecords<V3Donation>(requestUtils, '/givewp/v3/donations', {
                mode: 'live',
                status: 'any',
                anonymousDonations: 'include',
            });
            // The list leaves trashed donations out unless Trash is chosen.
            const expected = live.filter(
                (donation) =>
                    donation.status !== 'trash' &&
                    donation.createdAt.slice(0, 10) >= start &&
                    donation.createdAt.slice(0, 10) <= end
            ).length;

            await visit(page, admin, `${DONATIONS}&give-test-mode=0`);
            await setTestMode(page, false);
            await addFilter(page, 'Donation date');

            await popover(page).getByLabel('Start date').fill(start);
            await popover(page).getByLabel('End date').fill(end);

            const ranged = nextListRequest(page, DONATIONS_REQUEST, (url) => url.searchParams.has('start'));
            await popover(page).getByRole('button', {name: 'Apply'}).click();
            const response = await ranged;
            const url = new URL(response.url());

            expect(url.searchParams.get('start')).toBe(`${start}T00:00:00`);
            expect(url.searchParams.get('end')).toBe(`${end}T23:59:59`);
            expect(Number(((await response.json()) as {totalItems: number | string}).totalItems)).toBe(expected);
            await expect(page).toHaveURL(new RegExp(`give-date=${start}_${end}(&|$)`));
        });

        test('"Last 30 days" shows as the date chip', async ({page, admin}) => {
            await visit(page, admin, DONATIONS);
            await addFilter(page, 'Donation date');

            const sent = nextListRequest(page, DONATIONS_REQUEST, (url) => url.searchParams.has('start'));
            await popover(page).getByRole('button', {name: 'Last 30 days'}).click();
            await sent;

            await expect(chip(page, 'Donation date')).toHaveText('Donation date: Last 30 days');
            await expect(page).toHaveURL(/give-date=30(&|$)/);
        });

        test('an end date before the start date is explained and cannot be applied', async ({page, admin}) => {
            await visit(page, admin, DONATIONS);
            await addFilter(page, 'Donation date');

            await popover(page).getByLabel('Start date').fill(dayOfLastMonth(15));
            await popover(page).getByLabel('End date').fill(dayOfLastMonth(1));

            await expect(popover(page).getByRole('alert')).toHaveText(
                'The end date must be on or after the start date.'
            );
            await expect(popover(page).getByRole('button', {name: 'Apply'})).toBeDisabled();
        });

        test('removing the Test mode chip turns the header switch off', async ({page, admin}) => {
            await visit(page, admin, DONATIONS);
            await setTestMode(page, true);

            await expect(filterBar(page).getByText('Test mode', {exact: true})).toBeVisible();

            const live = nextListRequest(
                page,
                DONATIONS_REQUEST,
                (url) => url.searchParams.get('testMode') === 'false'
            );
            await filterBar(page).getByRole('button', {name: 'Remove Test mode'}).click();
            await live;

            await expect(page.getByRole('switch')).toHaveAttribute('aria-checked', 'false');
            await expect(filterBar(page).getByText('Test mode', {exact: true})).toHaveCount(0);
        });

        test('search matches a name from its start, not from its middle', async ({page, admin, requestUtils}) => {
            const live = await allRecords<V3Donation>(requestUtils, '/givewp/v3/donations', {
                mode: 'live',
                status: 'any',
                anonymousDonations: 'include',
                // Without it the v3 route shortens last names to an initial.
                includeSensitiveData: true,
            });
            const donation = live.find((record) => {
                const last = record.lastName ?? '';
                const middle = last.slice(1, 4).toLowerCase();
                const startsWithMiddle = [record.firstName, last, record.email].some((value) =>
                    (value ?? '').toLowerCase().startsWith(middle)
                );

                return record.status !== 'trash' && /^\p{L}{4,}$/u.test(last) && !startsWithMiddle;
            });
            test.skip(!donation, 'The site has no live donation with a last name to search for.');

            const last = donation!.lastName;

            await visit(page, admin, `${DONATIONS}&give-test-mode=0`);
            await setTestMode(page, false);

            const search = page.getByLabel('Search donations');
            const prefix = last.slice(0, 3);
            const byPrefix = nextListRequest(
                page,
                DONATIONS_REQUEST,
                (url) => url.searchParams.get('search') === prefix
            );
            await search.fill(prefix);
            await byPrefix;
            await expect(rowById(page, donation!.id)).toBeVisible();

            const middle = last.slice(1, 4);
            const byMiddle = nextListRequest(
                page,
                DONATIONS_REQUEST,
                (url) => url.searchParams.get('search') === middle
            );
            await search.fill(middle);
            await byMiddle;
            await expect(rowById(page, donation!.id)).toHaveCount(0);

            // The search term never goes in the address.
            expect(page.url()).not.toContain(`search=${middle}`);
        });
    });

    test.describe('Campaigns', () => {
        test.beforeEach(async ({requestUtils}) => {
            await resetLayout(requestUtils, 'campaigns');
        });

        test('the Status filter offers the four statuses admins set, named as the badges name them', async ({
            page,
            admin,
        }) => {
            await visit(page, admin, CAMPAIGNS);
            await chip(page, 'Status').click();

            await expect(popover(page).getByRole('checkbox')).toHaveCount(4);

            for (const label of ['Active', 'Inactive', 'Archived', 'Draft']) {
                await expect(popover(page).getByRole('checkbox', {name: label, exact: true})).toBeVisible();
            }
        });

        test('Draft shows only the draft campaigns, all of them', async ({page, admin, requestUtils}) => {
            const drafts = await allRecords<V3Campaign>(requestUtils, '/givewp/v3/campaigns', {status: ['draft']});

            await visit(page, admin, CAMPAIGNS);
            await chip(page, 'Status').click();

            const filtered = nextListRequest(
                page,
                CAMPAIGNS_REQUEST,
                (url) => url.searchParams.get('statuses') === 'draft'
            );
            await popover(page).getByRole('checkbox', {name: 'Draft', exact: true}).check();
            const response = await filtered;
            await page.keyboard.press('Escape');

            expect(Number(((await response.json()) as {totalItems: number | string}).totalItems)).toBe(drafts.length);

            if (drafts.length === 0) {
                return;
            }

            const rows = listRows(page);
            await expect(rows.first()).toBeVisible();

            for (const row of await rows.all()) {
                await expect(row).toContainText('Draft');
                await expect(row).not.toContainText(/\b(Active|Inactive|Archived)\b/);
            }
        });
    });

    test.describe('Campaign Forms tab', () => {
        test.beforeEach(async ({requestUtils}) => {
            await resetLayout(requestUtils, 'campaign-forms');
        });

        test('has no Campaign filter, and Reset keeps the campaign rows', async ({page, admin, requestUtils}) => {
            const campaigns = await allRecords<V3Campaign>(requestUtils, '/givewp/v3/campaigns', {status: ['active']});
            test.skip(campaigns.length === 0, 'The site has no active campaign.');

            const query = `${CAMPAIGNS}&id=${campaigns[0].id}&tab=forms`;
            const first = nextListRequest(page, FORMS_REQUEST);
            await visit(page, admin, query);
            const initial = Number(((await (await first).json()) as {totalItems: number | string}).totalItems);

            await expect(chip(page, 'Campaign')).toHaveCount(0);

            const addFilterButton = filterBar(page).getByRole('button', {name: 'Add filter'});

            if (await addFilterButton.isVisible()) {
                await addFilterButton.click();
                await expect(page.getByRole('menuitem', {name: 'Campaign', exact: true})).toHaveCount(0);
                await page.keyboard.press('Escape');
            }

            await chip(page, 'Status').click();
            const trashed = nextListRequest(page, FORMS_REQUEST, (url) => url.searchParams.get('status') === 'trash');
            await popover(page).getByRole('checkbox', {name: 'Trash', exact: true}).check();
            expect(new URL((await trashed).url()).searchParams.get('campaignId')).toBe(String(campaigns[0].id));
            await page.keyboard.press('Escape');

            // The list may answer Reset from its cache, so the rows are what is checked, not a new request.
            await filterBar(page).getByRole('button', {name: 'Reset', exact: true}).click();

            await expect(chip(page, 'Status')).toHaveText('Status');
            await expect(listRows(page)).toHaveCount(Math.min(initial, 30));
            await expect(page.getByText(/match your search or filters/)).toHaveCount(0);
        });
    });

    test.describe('Donors', () => {
        test.beforeEach(async ({requestUtils}) => {
            await resetLayout(requestUtils, 'donors');
        });

        test('search matches a name anywhere, as the hint says', async ({page, admin, requestUtils}) => {
            const donors = await allRecords<V3Donor>(requestUtils, '/givewp/v3/donors', {});
            const donor = donors.find((record) => /^\p{L}{6,}/u.test(record.name ?? ''));
            test.skip(!donor, 'The site has no donor with a name to search for.');

            await visit(page, admin, DONORS);

            const search = page.getByLabel('Search donors');
            await expect(search).toHaveAccessibleDescription(/anywhere/);

            // Letters from the middle of the name, not its start.
            const middle = donor!.name.slice(1, 6);
            const searched = nextListRequest(page, DONORS_REQUEST, (url) => url.searchParams.get('search') === middle);
            await search.fill(middle);
            const items = ((await (await searched).json()) as {items: Array<{id: number | string}>}).items;

            expect(items.map((item) => Number(item.id))).toContain(donor!.id);
            await expect(rowWithText(page, donor!.name).first()).toBeVisible();
        });
    });

    test.describe('Subscriptions @recurring', () => {
        test('Status keeps Trash alone', async ({page, admin, requestUtils}) => {
            const plugins = (await requestUtils.rest({path: '/wp/v2/plugins', params: {status: 'active'}})) as Array<{
                plugin: string;
            }>;
            test.skip(
                !plugins.some((plugin) => plugin.plugin.startsWith('give-recurring/')),
                'Give Recurring is not active.'
            );

            await resetLayout(requestUtils, 'subscriptions');
            await visit(page, admin, SUBSCRIPTIONS);
            await chip(page, 'Status').click();

            const first = popover(page)
                .locator('.givewp-options-filter-control__option')
                .filter({hasNotText: /^Trash$/})
                .first()
                .getByRole('checkbox');
            await first.check();
            await popover(page).getByRole('checkbox', {name: 'Trash', exact: true}).check();

            await expect(first).not.toBeChecked();
            await expect(popover(page).getByRole('checkbox', {checked: true})).toHaveCount(1);
        });
    });
});
