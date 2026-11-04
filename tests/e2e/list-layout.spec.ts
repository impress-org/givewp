import type {Locator, Page} from '@playwright/test';
import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation} from './utils/donation';
import {
    expectNoBrowserDialogs,
    listRows,
    listTable,
    rowById,
    rowWithText,
    selectionBar,
    visitList,
} from './utils/list-table';
import {resetLayout} from './utils/user';
import {wp} from './utils/wp-cli';

/**
 * How the admin list screens look and read: money and dates written the same way on every screen,
 * notices above the table and never inside it, stat tiles that stay on screen and filter the list,
 * and layouts that keep every column and action reachable from 1440 px down to 375 px.
 *
 * The formatters, the tile filter rules and the narrow field roles are covered by PHPUnit and Jest.
 * What only a browser shows is where things land on the page, so that is what these tests check.
 * Each test makes the records it needs, so a run means the same thing on a fresh site and on a
 * developer site full of other records.
 */
const DONATIONS = 'post_type=give_forms&page=give-payment-history';
const DONORS = 'post_type=give_forms&page=give-donors';
const CAMPAIGNS = 'post_type=give_forms&page=give-campaigns';
const FORMS = 'post_type=give_forms&page=give-forms';
const SUBSCRIPTIONS = 'post_type=give_forms&page=give-subscriptions';
const EVENT_TICKETS = 'post_type=give_forms&page=give-event-tickets';

const MONEY = /^\$[\d,]+\.\d{2}$/;
const DATE_TIME = /^[A-Z][a-z]+ \d{1,2}, \d{4} at \d{1,2}:\d{2} (am|pm)$/;

const ADMIN_USER_ID = '1';

/**
 * The text of the cell under the column headed `header` in `row`. DataViews gives cells no field
 * name, so the column is found by its position in the header row. The headers and the cell are read
 * in one step, because the columns can still change while the stored layout settles.
 */
function cellText(row: Locator, header: string): Promise<string> {
    return row.evaluate((element, name) => {
        const headers = Array.from(element.closest('table')?.querySelectorAll('thead th') ?? []).map((cell) =>
            // The DOM text, not the rendered text: headers are styled in capitals and carry a sort arrow.
            (cell.textContent ?? '').replace(/[↑↓]/g, '').trim()
        );
        const index = headers.indexOf(name);

        if (index < 0) {
            return `No "${name}" column; the columns are: ${headers.join(', ')}`;
        }

        return ((element.querySelectorAll('td')[index] as HTMLElement | undefined)?.innerText ?? '').trim();
    }, header);
}

/**
 * Checks the cell under `header` in `row` until it matches `pattern`.
 */
async function expectCell(row: Locator, header: string, pattern: RegExp): Promise<void> {
    await expect(row).toBeVisible();
    await expect.poll(() => cellText(row, header), {message: `${header} cell`}).toMatch(pattern);
}

/**
 * Whether the page scrolls sideways. Only the list's own scroll container may be wider than the window.
 */
function pageOverflows(page: Page): Promise<boolean> {
    return page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
}

/**
 * The top edge of `locator`, in page pixels.
 */
async function topOf(locator: Locator): Promise<number> {
    const box = await locator.boundingBox();

    expect(box, 'The element should be on the page').not.toBeNull();

    return box!.y;
}

function isPluginActive(slug: string): boolean {
    return wp('plugin', 'list', '--status=active', '--field=name').split(/\s+/).includes(slug);
}

function isEventTicketsEnabled(): boolean {
    return wp('eval', 'echo (int) Give\\BetaFeatures\\Facades\\FeatureFlag::eventTickets();') === '1';
}

test.describe('Money and dates', () => {
    test.use({locale: 'pt-BR'});

    test.beforeAll(() => {
        wp('option', 'update', 'date_format', 'F j, Y');
        wp('option', 'update', 'time_format', 'g:i a');
    });

    test('money and dates match across screens in a pt-BR browser', async ({page, admin, requestUtils}) => {
        expect(wp('eval', 'echo give_get_currency();'), 'This test expects a site in US dollars').toBe('USD');
        // A stored layout can reorder or hide the columns read below.
        for (const screen of ['donations', 'donors', 'campaigns']) {
            await resetLayout(requestUtils, screen);
        }

        const campaign = await createCampaignWithForm(requestUtils);
        const donationId = createDonation(campaign.formId);
        // The REST route leaves the email out by default, so it is read from the model.
        const email = wp('eval', `echo \\Give\\Donations\\Models\\Donation::find(${donationId})->email;`);
        expect(email).toContain('@');

        // The list request no longer carries the browser's language for the server to format with.
        const request = page.waitForRequest(/give-api\/v2\/admin\/donations\?/);
        await visitList(page, admin, DONATIONS);
        expect(new URL((await request).url()).searchParams.has('locale')).toBe(false);

        const donation = rowById(page, donationId);
        await expectCell(donation, 'Amount', MONEY);
        await expectCell(donation, 'Date', DATE_TIME);

        await visitList(page, admin, DONORS);
        // The Donors list shows no ID column by default; the email is in the Name cell.
        const donor = rowWithText(page, email);
        await expectCell(donor, 'Total Given', MONEY);
        await expectCell(donor, 'Latest Donation', DATE_TIME);

        await visitList(page, admin, CAMPAIGNS);
        const campaignRow = rowById(page, campaign.campaignId);
        await expectCell(campaignRow, 'Revenue', MONEY);
        await expectCell(campaignRow, 'Goal', /^\$[\d,]+\.\d{2} of \$[\d,]+\.\d{2}/);
    });
});

test.describe('Notices', () => {
    expectNoBrowserDialogs();

    test.beforeEach(() => {
        // Recommendation dismissals are stored per site; clear them so today's tip shows.
        wp(
            'option',
            'delete',
            'givewp_donations_recurring_recommendation_dismissed',
            'givewp_donations_fee_recovery_recommendation_dismissed',
            'givewp_donations_designated_funds_recommendation_dismissed'
        );
    });

    test('the recommendation is a notice above the table and the first row is a record', async ({
        page,
        admin,
        requestUtils,
    }) => {
        test.skip(
            isPluginActive('give-recurring') && isPluginActive('give-fee-recovery') && isPluginActive('give-funds'),
            'Every recommended add-on is active, so there is no tip to show'
        );

        createDonation((await createCampaignWithForm(requestUtils)).formId);

        const texts: string[] = [];

        for (let load = 0; load < 5; load++) {
            await visitList(page, admin, DONATIONS);

            const tip = page.getByRole('group', {name: 'Pro tip'});
            await expect(tip).toBeVisible();
            texts.push(await tip.innerText());

            // Above the table, never a row of it.
            const tipBox = await tip.boundingBox();
            expect(tipBox!.y + tipBox!.height).toBeLessThanOrEqual(await topOf(listRows(page).first()));
            await expect(listTable(page).locator('.dataviews-view-table tbody').getByRole('group')).toHaveCount(0);
            await expect(listRows(page).first().locator('.idBadge')).toHaveCount(1);
        }

        // One tip a day: every load shows the same one.
        expect(new Set(texts).size).toBe(1);
    });

    test('the Campaigns tip leaves select-all, bulk actions and the first title usable', async ({
        page,
        admin,
        requestUtils,
    }) => {
        wp('eval', `delete_user_meta(${ADMIN_USER_ID}, 'givewp_campaign_listtable_notice');`);
        await resetLayout(requestUtils, 'campaigns');

        const first = await createCampaignWithForm(requestUtils);
        await createCampaignWithForm(requestUtils);

        await visitList(page, admin, CAMPAIGNS);

        const tip = page.getByRole('group', {name: 'Campaign List'});
        await expect(tip).toBeVisible();

        // A plain click fails when something covers the target, so each click proves it is reachable.
        await listTable(page).getByRole('checkbox', {name: 'Select all'}).first().click();
        await expect(selectionBar(page)).toBeVisible();

        await selectionBar(page).getByRole('button', {name: 'Merge'}).click();
        await expect(page.getByRole('dialog')).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(page.getByRole('dialog')).toHaveCount(0);

        await listTable(page).getByRole('checkbox', {name: 'Deselect all'}).first().click();
        await expect(tip).toBeVisible();

        await rowById(page, first.campaignId).getByRole('link', {name: first.title}).first().click();
        await expect(page).toHaveURL(new RegExp(`id=${first.campaignId}`));
    });
});

test.describe('Stat tiles', () => {
    expectNoBrowserDialogs();

    test.beforeEach(async ({requestUtils}) => {
        await resetLayout(requestUtils, 'donations');
    });

    test('tiles stay mounted, filter and show their pressed state', async ({page, admin, requestUtils}) => {
        const donationId = createDonation((await createCampaignWithForm(requestUtils)).formId);

        await visitList(page, admin, DONATIONS);

        const tiles = page.getByRole('region', {name: 'Donations statistics'});
        await expect(tiles.locator('.givewp-stat-tile__value').first()).toBeVisible();
        const firstTile = await tiles.locator('.givewp-stat-tile').first().elementHandle();

        // A bulk action reloads the list and the numbers; the tiles must not unmount meanwhile.
        await rowById(page, donationId).getByRole('checkbox').check();
        await selectionBar(page).getByRole('button', {name: 'Set status'}).click();
        const dialog = page.getByRole('dialog', {name: 'Set status'});
        await dialog.getByLabel('Status').selectOption({label: 'Pending'});
        const reloaded = page.waitForResponse(/give-api\/v2\/admin\/donations\/stats/);
        await dialog.getByRole('button', {name: 'Set status'}).click();
        await reloaded;

        expect(await firstTile!.evaluate((element) => element.isConnected)).toBe(true);
        await expect(tiles.locator('.givewp-skeleton')).toHaveCount(0);

        const recurring = await page.evaluate(() =>
            Boolean(
                (window as {GiveDonations?: {recurringDonationsEnabled?: boolean}}).GiveDonations
                    ?.recurringDonationsEnabled
            )
        );

        if (!recurring) {
            // Without recurring giving there is no Donation type filter: the Recurring tile is an upgrade, not a button.
            const upgrade = tiles.locator('.givewp-stat-tile', {hasText: 'Recurring Donations'});
            await expect(upgrade.getByRole('link', {name: 'Upgrade'})).toBeVisible();
            await expect(tiles.getByRole('button')).toHaveCount(0);
            test.info().annotations.push({
                type: 'skipped part',
                description: 'Tile filtering needs Give Recurring, which is not active on this site',
            });

            return;
        }

        const oneTime = tiles.getByRole('button', {name: /One-Time Donations/});
        await expect(oneTime).toHaveAttribute('aria-pressed', 'false');
        await oneTime.click();

        await expect(oneTime).toHaveAttribute('aria-pressed', 'true');
        await expect(page.getByRole('group', {name: 'Filters'})).toContainText('Donation type: One-time');
    });
});

test.describe('Wide and narrow screens', () => {
    expectNoBrowserDialogs();

    test('the last column is reachable at 1024 px', async ({page, admin, requestUtils}) => {
        await resetLayout(requestUtils, 'donors');
        await resetLayout(requestUtils, 'donation-forms');
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        await page.setViewportSize({width: 1024, height: 900});

        for (const query of [DONORS, FORMS]) {
            await visitList(page, admin, query);

            const root = listTable(page);
            await expect(root).toHaveClass(/givewp-list-table--more-end/);

            await root.locator('.dataviews-wrapper').evaluate((element) => {
                element.scrollLeft = element.scrollWidth;
            });

            await expect(root.locator('.dataviews-view-table thead th').last()).toBeInViewport();
            await expect(root).not.toHaveClass(/givewp-list-table--more-end/);
            expect(await pageOverflows(page)).toBe(false);
        }
    });

    test('782 px and 375 px stack in order with no page overflow', async ({page, admin, requestUtils}) => {
        const campaign = await createCampaignWithForm(requestUtils);
        createDonation(campaign.formId);

        /*
         * `pageFits: false` checks only the list area. The campaign details page header (shared by every
         * details page, outside the list shell) keeps the campaign title and status badge on one line,
         * which is wider than 375 px for a long title.
         */
        const screens: Array<{query: string; key: string; hasRecords: boolean; pageFits: boolean}> = [
            {query: CAMPAIGNS, key: 'campaigns', hasRecords: true, pageFits: true},
            {query: FORMS, key: 'donation-forms', hasRecords: true, pageFits: true},
            {
                query: `${CAMPAIGNS}&id=${campaign.campaignId}&tab=forms`,
                key: 'campaign-forms',
                hasRecords: true,
                pageFits: false,
            },
            {query: DONATIONS, key: 'donations', hasRecords: true, pageFits: true},
            {query: DONORS, key: 'donors', hasRecords: true, pageFits: true},
        ];

        // These may have nothing to list on a test site, and then show a blank slate rather than cards.
        if (isPluginActive('give-recurring')) {
            screens.push({query: SUBSCRIPTIONS, key: 'subscriptions', hasRecords: false, pageFits: true});
        }

        if (isEventTicketsEnabled()) {
            screens.push({query: EVENT_TICKETS, key: 'event-tickets', hasRecords: false, pageFits: true});
        }

        for (const width of [782, 375]) {
            await page.setViewportSize({width, height: 900});

            for (const {query, key, hasRecords, pageFits} of screens) {
                const label = `${key} at ${width} px`;

                await resetLayout(requestUtils, key);
                await admin.visitAdminPage('edit.php', query);

                const list = listTable(page);

                if (hasRecords) {
                    // One card per record, and no table.
                    await expect(list.locator('.dataviews-view-grid'), label).toBeVisible({timeout: 20_000});
                    await expect(list.locator('.dataviews-view-table'), label).toHaveCount(0);
                } else {
                    await page.waitForLoadState('networkidle');
                }

                if (pageFits) {
                    expect(await pageOverflows(page), `${label} scrolls sideways`).toBe(false);
                } else {
                    const right = await list.evaluate((element) => element.getBoundingClientRect().right);
                    expect(right, `${label}: the list is wider than the window`).toBeLessThanOrEqual(width);
                }
            }
        }

        // The stacking order, on the screen that has every part: title, test-mode switch, tiles,
        // notices, toolbar, then the cards.
        await page.setViewportSize({width: 782, height: 900});
        await admin.visitAdminPage('edit.php', DONATIONS);
        await expect(listTable(page).locator('.dataviews-view-grid')).toBeVisible({timeout: 20_000});

        const parts = [
            page.getByRole('heading', {level: 1, name: 'Donations'}),
            page.getByRole('switch'),
            page.getByRole('region', {name: 'Donations statistics'}),
            page.locator('.givewp-list-table-notice-area'),
            page.getByRole('group', {name: 'Filters'}),
            listTable(page).locator('.dataviews-view-grid'),
        ];
        const tops: number[] = [];

        for (const part of parts) {
            if ((await part.count()) > 0) {
                tops.push(await topOf(part.first()));
            }
        }

        expect(tops).toEqual([...tops].sort((a, b) => a - b));
    });

    test('Forms at 1440 px: names on two lines, dash for no goal, pill on the title line', async ({
        page,
        admin,
        requestUtils,
    }) => {
        await resetLayout(requestUtils, 'donation-forms');

        const longName = `E2E campaign with a long name ${Date.now()}`.padEnd(40, 'x').slice(0, 40);
        const named = await createCampaignWithForm(requestUtils, {title: longName});
        const noGoal = await createCampaignWithForm(requestUtils);
        wp(
            'eval',
            `$form = \\Give\\DonationForms\\Models\\DonationForm::find(${noGoal.formId});
            $form->settings->enableDonationGoal = false;
            $form->save();`
        );

        await page.setViewportSize({width: 1440, height: 900});
        await visitList(page, admin, FORMS);

        // The campaign name wraps to two lines at most.
        const name = rowById(page, named.formId).locator('.givewp-truncated-text');
        await expect(name).toBeVisible();
        const lines = await name.evaluate((element) => {
            const lineHeight = parseFloat(getComputedStyle(element).lineHeight);

            return Math.round(element.getBoundingClientRect().height / lineHeight);
        });
        expect(lines).toBeLessThanOrEqual(2);
        await expect(name).toHaveAttribute('title', longName);

        // No goal: a dash, named for screen readers, and never the old "No Goal Set" text.
        const noGoalRow = rowById(page, noGoal.formId);
        await expect(noGoalRow.locator('.givewp-list-table-dash')).toContainText('No goal set');
        await expect(listTable(page)).not.toContainText('No Goal Set');

        // The "Default" pill flows on the title line rather than on a line of its own.
        const title = rowById(page, named.formId).locator('.interweave').first();
        const pill = rowById(page, named.formId).locator('.givewp-default-form-pill');
        await expect(pill).toBeVisible();
        const titleBox = (await title.boundingBox())!;
        const pillBox = (await pill.boundingBox())!;
        expect(pillBox.y).toBeLessThan(titleBox.y + titleBox.height);
    });
});

test.describe('Legacy view', () => {
    expectNoBrowserDialogs();

    test.afterEach(async ({requestUtils}) => {
        await requestUtils.rest({path: '/give-api/v2/admin/donations/view', params: {isLegacy: 0}});
    });

    test('Switch to Legacy View lives in the view menu', async ({page, admin, requestUtils}) => {
        await resetLayout(requestUtils, 'donations');
        await visitList(page, admin, DONATIONS);

        // Not in the page header any more.
        await expect(
            page.locator('.wrap header, [class*="pageHeader"]').getByText('Switch to Legacy View')
        ).toHaveCount(0);

        await listTable(page).getByRole('button', {name: 'More view options'}).click();
        const item = page.getByRole('menuitem', {name: 'Switch to the legacy donations table view'});
        await expect(item).toBeVisible();

        await Promise.all([page.waitForEvent('load'), item.click()]);
        await expect(listTable(page)).toHaveCount(0);
    });
});
