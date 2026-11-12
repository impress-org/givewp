import type {Page} from '@playwright/test';
import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation, SEEDED_FIRST_NAME, SEEDED_LAST_NAME} from './utils/donation';
import {failedCalls, watchRestCalls} from './utils/rest';
import {createStandaloneForm} from './utils/standalone-form';
import {isPluginActive, saveOptions, wp} from './utils/wp-cli';
import {
    addFilter,
    allRecords,
    applyFilter,
    chip,
    DATE_TIME,
    dayOfLastMonth,
    expectCell,
    expectListMounts,
    expectNoBrowserDialogs,
    expectStacksOnNarrowScreens,
    filterBar,
    listRows,
    listTable,
    MONEY,
    nextListRequest,
    popover,
    rowById,
    selectionBar,
    setTestMode,
    topOf,
    useUsDateFormats,
    visitList,
} from './utils/list-table';
import {adminInNewBrowser, deleteUser, isLayoutWrite, resetLayout, signInNewUser} from './utils/user';

/**
 * The Donations list: which form a donation went to, and filtering the list by it.
 *
 * Now that a form can live without a campaign, "No campaign" says nothing about where a donation
 * came from, so the list names the form and filters by it. The column's rules and the endpoint's
 * `formId` filter are covered by PHPUnit; what is not is whether the list reaches the route with
 * what the admin picked, whether the form filter finds every form and fits the page, and whether
 * the list narrows to what was picked. The list renders through `@wordpress/dataviews`.
 *
 * Donations are made with `createDonation` and forms inside each test, so a run means the same thing
 * on a fresh install and on a developer site full of other donations.
 */
const DONATIONS_LIST = 'post_type=give_forms&page=give-payment-history';
const LIST_REQUEST = /admin\/donations\?/;
const DONATIONS_REQUEST = /give-api\/v2\/admin\/donations\?/;
const OPTIONS_REQUEST = /list-table-filter-options\//;

type V3Donation = {id: number; createdAt: string; status: string};

test.describe('Donations list', () => {
    test('renders through the list engine without errors', async ({page, admin}) => {
        await expectListMounts(page, admin, DONATIONS_LIST, 'Donations');
    });

    test('shows the form on each donation, and "No campaign" beside a standalone form', async ({
        page,
        admin,
        requestUtils,
    }) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const {formId, title} = await createStandaloneForm(page, admin);
        const standalone = createDonation(formId);
        const inCampaign = createDonation(campaign.formId);

        await visitDonations(page, admin);

        const standaloneRow = rowById(page, standalone);
        await expect(standaloneRow).toContainText('No campaign');
        await expect(standaloneRow.getByRole('link', {name: title})).toHaveAttribute(
            'href',
            new RegExp(`post\\.php\\?post=${formId}&action=edit`)
        );

        // A donation in a campaign names the campaign as well as the form, and each links to its own screen.
        const campaignRow = rowById(page, inCampaign);
        await expect(campaignRow).not.toContainText('No campaign');
        await expect(campaignRow.locator('a.campaignLink')).toHaveText(campaign.title);
        await expect(campaignRow.locator('a.campaignLink')).toHaveAttribute(
            'href',
            new RegExp(`page=give-campaigns&id=${campaign.campaignId}`)
        );
        await expect(campaignRow.locator('a:not(.campaignLink)', {hasText: campaign.title})).toHaveAttribute(
            'href',
            new RegExp(`post\\.php\\?post=${campaign.formId}&action=edit`)
        );
    });

    test('filters by form and combines with the campaign filter', async ({page, admin, requestUtils}) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const {formId, title} = await createStandaloneForm(page, admin);
        const standalone = createDonation(formId);
        const inCampaign = createDonation(campaign.formId);

        const calls = watchRestCalls(page);
        await visitDonations(page, admin);

        await expect(rowById(page, standalone)).toBeVisible();
        await expect(rowById(page, inCampaign)).toBeVisible();

        await applyFilter(page, 'Form', title);
        await expect(rowById(page, standalone)).toBeVisible();
        await expect(rowById(page, inCampaign)).toHaveCount(0);

        // The standalone form's donations are the ones with no campaign, so the two filters agree. The
        // request has to carry both filters: dropping the form one would still leave this row showing.
        const reloaded = page.waitForResponse(
            (response) => response.url().includes('campaignId=none') && response.url().includes(`formId=${formId}`)
        );
        await applyFilter(page, 'Campaign', 'No campaign');
        await reloaded;
        await expect(rowById(page, standalone)).toBeVisible();

        // A campaign's form and "No campaign" share no donations, so the list empties and says why. The
        // Form filter takes several forms, so it is reset before the other one is picked.
        await filterBar(page).getByRole('button', {name: /^Reset filter: Form/}).click();
        await applyFilter(page, 'Form', campaign.title);
        await expect(rowById(page, standalone)).toHaveCount(0);
        await expect(rowById(page, inCampaign)).toHaveCount(0);
        await expect(listTable(page)).toContainText('No donations match your search or filters.');

        expect(
            calls.some((call) => call.url.includes(`formId=${formId}`)),
            'The filter should send the form it was given'
        ).toBe(true);
        expect(failedCalls(calls)).toEqual([]);
    });

    test('lists a draft form so its donations can still be filtered', async ({page, admin, requestUtils}) => {
        const {formId, title} = await createStandaloneForm(page, admin);
        const donation = createDonation(formId);

        // A form taken offline keeps its donations, and archiving a campaign takes its forms offline.
        await requestUtils.rest({method: 'POST', path: `/wp/v2/give_forms/${formId}`, data: {status: 'draft'}});

        await visitDonations(page, admin);
        await applyFilter(page, 'Form', title, `${title} (Draft)`);

        await expect(rowById(page, donation)).toBeVisible();
    });

    test('finds a form past the first page of options by searching on the server', async ({
        page,
        admin,
        requestUtils,
    }) => {
        const prefix = `Paging form ${Date.now()}`;

        // The list waits for a donation row before the filters are used, so a fresh install needs one.
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        // The option route pages at 20, so the 25th form is not on the first page of options.
        const ids = wp(
            'eval',
            `$ids = []; for ($i = 1; $i <= 25; $i++) { $ids[] = wp_insert_post(['post_type' => 'give_forms', 'post_status' => 'publish', 'post_title' => sprintf('${prefix} %03d', $i)]); } echo implode(' ', $ids);`
        );

        try {
            await visitDonations(page, admin);

            // Typing the name asks the server, so the form is found without loading the pages before it.
            const filtered = page.waitForResponse((response) => response.url().includes('formId='));
            await applyFilter(page, 'Form', `${prefix} 025`);
            await filtered;
        } finally {
            // The forms would pile up on a developer's site and push newer forms past the filter's option cap.
            wp('post', 'delete', ...ids.split(' '), '--force');
        }
    });

    test('opens the form filter without adding a horizontal scrollbar at a narrow width', async ({
        page,
        admin,
        requestUtils,
    }) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);
        await page.setViewportSize({width: 1000, height: 800});

        await visitDonations(page, admin);
        await chip(page, 'Form').click();
        await expect(popover(page).getByRole('checkbox').first()).toBeVisible();

        const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);

        expect(overflow, 'The open filter should not make the page scroll sideways').toBeLessThanOrEqual(0);
    });

    test('selects only the filtered donations for a bulk action', async ({page, admin, requestUtils}) => {
        const campaign = await createCampaignWithForm(requestUtils);
        const {formId, title} = await createStandaloneForm(page, admin);
        const standalone = createDonation(formId);
        const inCampaign = createDonation(campaign.formId);

        await visitDonations(page, admin);
        await applyFilter(page, 'Form', title);
        await expect(rowById(page, inCampaign)).toHaveCount(0);

        await rowById(page, standalone).getByRole('checkbox').check();
        await expect(selectionBar(page)).toContainText('1 Item selected');

        // The action is read, not run: the confirmation lists what it would apply to, then it is cancelled.
        await selectionBar(page).getByRole('button', {name: 'Resend Email Receipts'}).click();

        const dialog = page.getByRole('dialog', {name: 'Resend Email Receipts'});
        await expect(dialog).toContainText(String(standalone));
        await expect(dialog).not.toContainText(String(inCampaign));

        await dialog.getByRole('button', {name: 'Cancel'}).click();
        await expect(selectionBar(page)).toContainText('1 Item selected');
    });
});

/**
 * The behavior every list screen shares, checked here because Donations has the most of each: row
 * actions without hovering, filter chips, the empty result, server sorting, page size, and the
 * selection count. The adapters behind them are covered by Jest.
 */
test.describe('Donations list behavior', () => {
    test('shows row actions without hovering: a menu on every row, Edit inline', async ({
        page,
        admin,
        requestUtils,
    }) => {
        const donation = createDonation((await createCampaignWithForm(requestUtils)).formId);

        await visitDonations(page, admin);
        const row = rowById(page, donation);

        // The menu button is always visible; the inline "Edit" shows on hover or focus, as in WordPress core.
        await page.mouse.move(0, 0);
        await expect(row.getByRole('button', {name: 'Actions', exact: true})).toBeVisible();
        await expect(row.getByRole('button', {name: 'Edit', exact: true})).toHaveCount(1);

        await row.getByRole('button', {name: 'Actions', exact: true}).click();
        await expect(page.getByRole('menuitem', {name: 'Trash'})).toBeVisible();
        await page.keyboard.press('Escape');

        // A trashed donation has "Restore" in place of "Edit", and "Delete permanently" in its menu.
        await row.getByRole('button', {name: 'Actions', exact: true}).click();
        await page.getByRole('menuitem', {name: 'Trash'}).click();
        await page.getByRole('dialog', {name: 'Trash donations'}).getByRole('button', {name: 'Move to trash'}).click();
        await expect(rowById(page, donation)).toHaveCount(0);

        // Trash is a Status value that cannot be combined with another one.
        await applyFilter(page, 'Status', 'Trash');
        const trashed = rowById(page, donation);
        await expect(trashed.getByRole('button', {name: 'Restore', exact: true})).toHaveCount(1);
        await trashed.getByRole('button', {name: 'Actions', exact: true}).click();
        await expect(page.getByRole('menuitem', {name: 'Delete permanently'})).toBeVisible();
        await page.getByRole('menuitem', {name: 'Restore'}).click();
        await page.getByRole('dialog', {name: 'Restore donations'}).getByRole('button', {name: 'Restore'}).click();
        await expect(rowById(page, donation)).toHaveCount(0);
    });

    test('shows applied filters as chips and Reset clears them but keeps the sort', async ({
        page,
        admin,
        requestUtils,
    }) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        await visitDonations(page, admin);
        await sortBy(page, 'Amount', 'Sort ascending');

        await applyFilter(page, 'Status', 'Completed');
        await expect(chip(page, 'Status')).toContainText('Completed');

        // The list may answer Reset from its cache (it already asked for this sort without a status),
        // so the address and the screen are checked, not a new request.
        await listTable(page).getByRole('button', {name: 'Reset', exact: true}).click();

        // Status is a primary filter: its chip stays, with no value.
        await expect(chip(page, 'Status')).toHaveText('Status');
        await expect(page).not.toHaveURL(/give-status=/);
        await expect(page).toHaveURL(/give-orderby=amount&give-order=asc/);
        await expect(page.locator('th[aria-sort="ascending"]')).toContainText('Amount');
    });

    test('says no records match a search, and Clear filters clears it', async ({page, admin, requestUtils}) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        await visitDonations(page, admin);
        const toggle = page.getByRole('switch', {name: 'View test donations'});
        const siteTestMode = await toggle.getAttribute('aria-checked');

        await listTable(page).getByRole('searchbox').fill('zzzzqqq');

        await expect(listTable(page)).toContainText('No donations match your search or filters.');
        await listTable(page).getByRole('button', {name: 'Clear filters'}).click();

        // Clear filters goes back to the screen's defaults, so test mode stays as the site has it.
        await expect(listTable(page).getByRole('searchbox')).toHaveValue('');
        await expect(toggle).toHaveAttribute('aria-checked', siteTestMode ?? 'false');
        await expect(listTable(page)).not.toContainText('No donations match your search or filters.');
    });

    test('sorts on the server and names the sorted column', async ({page, admin, requestUtils}) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        await visitDonations(page, admin);
        const sorted = page.waitForRequest((request) => request.url().includes('sortColumn=amount'));
        await sortBy(page, 'Amount', 'Sort ascending');
        await sorted;

        await expect(page.locator('th[aria-sort="ascending"]')).toHaveCount(1);
        await expect(page.locator('th[aria-sort="ascending"]')).toContainText('Amount');
        await expect(page.locator('[aria-label="sort"]')).toHaveCount(0);
    });

    test('changes the page size from the view options', async ({page, admin, requestUtils}) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        await visitDonations(page, admin);
        await listTable(page).getByRole('button', {name: 'View options', exact: true}).click();

        const resized = page.waitForRequest((request) => request.url().includes('perPage=10'));
        await page.getByRole('radio', {name: '10', exact: true}).click();
        const request = await resized;

        expect(request.url()).toContain('page=1');
    });

    test('shows how many rows are selected', async ({page, admin, requestUtils}) => {
        const formId = (await createCampaignWithForm(requestUtils)).formId;
        const first = createDonation(formId);
        const second = createDonation(formId);

        await visitDonations(page, admin);
        await rowById(page, first).getByRole('checkbox').check();
        await rowById(page, second).getByRole('checkbox').check();

        await expect(selectionBar(page)).toContainText('2 Items selected');
        await expect(selectionBar(page).getByRole('button', {name: 'Trash'})).toBeVisible();
        await expect(selectionBar(page).getByRole('button', {name: 'Restore'})).toHaveCount(0);
    });
});

/**
 * The layout (columns, density, page size) is stored per user per screen on the server, so it follows
 * the admin to another browser and does not reach anyone else. The record's rules are covered by
 * PHPUnit and the merge with the defaults by Jest.
 */
test.describe('Donations list layout', () => {
    test.beforeEach(async ({requestUtils}) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);
        await resetLayout(requestUtils, 'donations');
    });

    test.afterEach(async ({requestUtils}) => {
        await resetLayout(requestUtils, 'donations');
    });

    test('follows the admin to a second browser and not to a second user', async ({
        page,
        admin,
        browser,
        requestUtils,
    }) => {
        await visitDonations(page, admin);

        const stored = page.waitForResponse(
            (response) => response.url().includes('list-table-layouts/donations') && isLayoutWrite(response.request())
        );
        await listTable(page).getByRole('button', {name: 'View options', exact: true}).click();
        await page.getByRole('radio', {name: 'Compact', exact: true}).click();
        await page.keyboard.press('Escape');
        expect((await stored).ok()).toBe(true);
        await expect(page.locator('.dataviews-view-table.has-compact-density')).toBeVisible();

        const secondBrowser = await adminInNewBrowser(browser);
        const samePerson = await secondBrowser.newPage();
        await visitDonations(samePerson);
        await expect(samePerson.locator('.dataviews-view-table.has-compact-density')).toBeVisible();
        await secondBrowser.close();

        const otherAdmin = await signInNewUser(browser, requestUtils, 'administrator');
        await visitDonations(otherAdmin.page);
        await expect(otherAdmin.page.locator('.dataviews-view-table')).toBeVisible();
        await expect(otherAdmin.page.locator('.dataviews-view-table.has-compact-density')).toHaveCount(0);
        await deleteUser(requestUtils, otherAdmin);
    });

    test('shows Payment Type by default only when recurring giving is available', async ({page, admin}) => {
        await visitDonations(page, admin);

        const recurring = await page.evaluate(() =>
            Boolean(
                (window as {GiveDonations?: {recurringDonationsEnabled?: boolean}}).GiveDonations
                    ?.recurringDonationsEnabled
            )
        );
        const header = page.locator('.dataviews-view-table thead th', {hasText: 'Payment Type'});

        await expect(header).toHaveCount(recurring ? 1 : 0);
    });
});

test.describe('Donations list view in the address', () => {
    test.beforeEach(async ({requestUtils}) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);
        await resetLayout(requestUtils, 'donations');
    });

    test.afterEach(async ({requestUtils}) => {
        // Page size changes are stored as the admin's layout too.
        await resetLayout(requestUtils, 'donations');
    });

    test('opens a clean address in the default view', async ({page, admin}) => {
        await visitDonations(page, admin);

        expect(giveParameters(page)).toEqual([]);
    });

    test('keeps the view on reload, with the first request already carrying it', async ({page, admin}) => {
        await visitDonations(page, admin);
        await sortBy(page, 'Amount', 'Sort ascending');
        await applyFilter(page, 'Status', 'Completed');

        await expect(page).toHaveURL(/give-orderby=amount&give-order=asc/);
        await expect(page).toHaveURL(/give-status=publish/);

        const firstRequest = page.waitForRequest(LIST_REQUEST);
        await page.reload();
        const request = await firstRequest;

        expect(request.url()).toContain('sortColumn=amount');
        expect(request.url()).toContain('sortDirection=asc');
        expect(request.url()).toContain('status=publish');
        await expect(page.locator('th[aria-sort="ascending"]')).toContainText('Amount');
        await expect(chip(page, 'Status')).toContainText('Completed');
    });

    test('steps back and forward one change at a time without reloading the page', async ({page, admin}) => {
        await visitDonations(page, admin);
        await page.evaluate(() => Object.assign(window, {givewpSamePage: true}));

        const addresses = [page.url()];
        for (const size of ['10', '20', '50', '100', '10']) {
            await choosePageSize(page, size);
            await expect(page).not.toHaveURL(addresses[addresses.length - 1]);
            addresses.push(page.url());
        }

        for (let step = addresses.length - 2; step >= 0; step--) {
            await page.goBack();
            await expect(page).toHaveURL(addresses[step]);
        }

        for (let step = 1; step < addresses.length; step++) {
            await page.goForward();
            await expect(page).toHaveURL(addresses[step]);
        }

        await expect(page).toHaveURL(/give-per-page=10/);
        expect(await page.evaluate(() => 'givewpSamePage' in window), 'Back and forward should not reload').toBe(true);
    });

    test('never writes the search to the address, and a new page opens without it', async ({page, admin, context}) => {
        await visitDonations(page, admin);
        await search(page, 'Eve');

        expect(page.url()).not.toContain('Eve');

        const other = await context.newPage();
        await other.goto(page.url());
        await expect(listRows(other).first()).toBeVisible({timeout: 15_000});
        await expect(listTable(other).getByRole('searchbox')).toHaveValue('');
        await other.close();
    });

    test('keeps the search on reload of the same tab', async ({page, admin}) => {
        // Core's admin canonical-URL script clears history.state on load; the tab's session copy restores it.
        await visitDonations(page, admin);
        await search(page, 'Eve');

        await page.reload();
        await expect(listTable(page).getByRole('searchbox')).toHaveValue('Eve');
    });

    test('rewrites a legacy donor link to the prefixed parameter without a new history entry', async ({
        page,
        admin,
        requestUtils,
    }) => {
        const donationId = createDonation((await createCampaignWithForm(requestUtils)).formId);
        const {donorId} = await requestUtils.rest({path: `/givewp/v3/donations/${donationId}`});

        await page.goto('/wp-admin/');
        const historyLength = await page.evaluate(() => history.length);

        await admin.visitAdminPage('admin.php', `page=give-payment-history&donor=${donorId}`);
        await expect(listRows(page).first()).toBeVisible({timeout: 15_000});

        await expect(page).toHaveURL(new RegExp(`[?&]give-donor=${donorId}(&|$)`));
        expect(new URL(page.url()).searchParams.has('donor')).toBe(false);
        expect(await page.evaluate(() => history.length)).toBe(historyLength + 1);
    });

    test('writes test mode only when it differs from the site mode', async ({page, admin}) => {
        await visitDonations(page, admin);

        const toggle = page.getByRole('switch', {name: 'View test donations'});
        const siteTestMode = (await toggle.getAttribute('aria-checked')) === 'true';

        expect(page.url()).not.toContain('give-test-mode');

        await toggle.click();
        await expect(page).toHaveURL(new RegExp(`give-test-mode=${siteTestMode ? '0' : '1'}`));

        await toggle.click();
        await expect(page).not.toHaveURL(/give-test-mode/);
    });

    test('a user without access to the screen gets the capability error and no list requests', async ({
        browser,
        requestUtils,
    }) => {
        const subscriber = await signInNewUser(browser, requestUtils, 'subscriber');
        const listRequests: string[] = [];
        subscriber.page.on('request', (request) => {
            if (/admin\/donations|list-table-layouts/.test(request.url())) {
                listRequests.push(request.url());
            }
        });

        await subscriber.page.goto(`/wp-admin/edit.php?${DONATIONS_LIST}&give-status=publish&give-paged=2`);

        await expect(subscriber.page.locator('body')).toContainText('Sorry, you are not allowed to access this page.');
        await expect(listTable(subscriber.page)).toHaveCount(0);
        expect(listRequests).toEqual([]);

        await deleteUser(requestUtils, subscriber);
    });
});

/**
 * The shared list filters on Donations: the filter bar, its chips, "Add filter", the date range, the
 * labeled search.
 *
 * The unit tests cover the declarations, the URL rules and the request builder. What they cannot
 * cover is the whole loop in a browser: a click in the bar reaching the endpoint with the right
 * parameters, and the endpoint answering with the rows the admin expects. Expected counts come from
 * the v3 REST API, which does not share the list endpoints' filter code, never from a hard-coded
 * number. No test runs a bulk action or any action that opens a browser dialog.
 */
test.describe('Donations list filters', () => {
    expectNoBrowserDialogs();

    test.beforeEach(async ({requestUtils}) => {
        await resetLayout(requestUtils, 'donations');
    });

    test('Status takes several values and the chip collapses past three', async ({page, admin}) => {
        await visitList(page, admin, DONATIONS_LIST);
        await chip(page, 'Status').click();

        // More than four options: the fifth one shows. This retries while the popover renders.
        await expect(popover(page).getByRole('checkbox').nth(4)).toBeVisible();

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
        await visitList(page, admin, DONATIONS_LIST);
        await chip(page, 'Status').click();

        const first = popover(page)
            .locator('.givewp-options-filter-control__option')
            .filter({hasNotText: /^Trash$/})
            .first()
            .getByRole('checkbox');
        await first.check();

        const trashOnly = nextListRequest(page, DONATIONS_REQUEST, (url) => url.searchParams.get('status') === 'trash');
        await popover(page).getByRole('checkbox', {name: 'Trash', exact: true}).check();
        await trashOnly;

        await expect(first).not.toBeChecked();
        await expect(popover(page).getByRole('checkbox', {checked: true})).toHaveCount(1);
    });

    test('"No campaign" cannot be combined with a campaign', async ({page, admin, requestUtils}) => {
        const {title} = await createCampaignWithForm(requestUtils);

        await visitList(page, admin, DONATIONS_LIST);
        await chip(page, 'Campaign').click();
        await popover(page).getByRole('searchbox').fill(title);

        const campaign = popover(page).getByRole('checkbox', {name: title, exact: true});
        await campaign.check();

        const none = nextListRequest(page, DONATIONS_REQUEST, (url) => url.searchParams.get('campaignId') === 'none');
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
        // A donation of its own makes sure the site has a donor (and an email) to search past.
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        const urls: string[] = [];
        page.on('request', (request) => {
            if (DONATIONS_REQUEST.test(request.url()) || OPTIONS_REQUEST.test(request.url())) {
                urls.push(request.url());
            }
        });

        await visitList(page, admin, DONATIONS_LIST);
        await addFilter(page, 'Donor');

        const term = SEEDED_FIRST_NAME;
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
        // One live donation inside the range and one just after it, so the count proves something on
        // an empty site too. The dates are in the site's time zone.
        const {formId} = await createCampaignWithForm(requestUtils);
        createDonation(formId, {mode: 'live', createdAt: `${dayOfLastMonth(5)} 12:00:00`});
        createDonation(formId, {mode: 'live', createdAt: `${dayOfLastMonth(16)} 00:30:00`});

        const live = await allRecords<V3Donation>(requestUtils, '/givewp/v3/donations', {
            mode: 'live',
            status: 'any',
            anonymousDonations: 'include',
        });
        // The list leaves trashed donations out unless Trash is chosen. `createdAt` is the site's
        // local time, as is `post_date`, which the list compares with the chosen days; both sides use
        // the same day strings, so the browser's time zone does not change the result.
        const expected = live.filter(
            (donation) =>
                donation.status !== 'trash' &&
                donation.createdAt.slice(0, 10) >= start &&
                donation.createdAt.slice(0, 10) <= end
        ).length;

        await visitList(page, admin, `${DONATIONS_LIST}&give-test-mode=0`);
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
        expect(expected).toBeGreaterThanOrEqual(1);
        expect(Number(((await response.json()) as {totalItems: number | string}).totalItems)).toBe(expected);
        await expect(page).toHaveURL(new RegExp(`give-date=${start}_${end}(&|$)`));
    });

    test('"Last 30 days" shows as the date chip', async ({page, admin}) => {
        await visitList(page, admin, DONATIONS_LIST);
        await addFilter(page, 'Donation date');

        const sent = nextListRequest(page, DONATIONS_REQUEST, (url) => url.searchParams.has('start'));
        await popover(page).getByRole('button', {name: 'Last 30 days'}).click();
        await sent;

        await expect(chip(page, 'Donation date')).toHaveText('Donation date: Last 30 days');
        await expect(page).toHaveURL(/give-date=30(&|$)/);
    });

    test('an end date before the start date is explained and cannot be applied', async ({page, admin}) => {
        await visitList(page, admin, DONATIONS_LIST);
        await addFilter(page, 'Donation date');

        await popover(page).getByLabel('Start date').fill(dayOfLastMonth(15));
        await popover(page).getByLabel('End date').fill(dayOfLastMonth(1));

        await expect(popover(page).getByRole('alert')).toHaveText('The end date must be on or after the start date.');
        await expect(popover(page).getByRole('button', {name: 'Apply'})).toBeDisabled();
    });

    test('search matches a name from its start, not from its middle', async ({page, admin, requestUtils}) => {
        // A test donation by "Eve Explorer": "Exp" starts the last name, "xpl" starts no name or email.
        const donation = createDonation((await createCampaignWithForm(requestUtils)).formId);
        const last = SEEDED_LAST_NAME;

        await visitList(page, admin, `${DONATIONS_LIST}&give-test-mode=1`);
        await setTestMode(page, true);

        const search = page.getByLabel('Search donations');
        const prefix = last.slice(0, 3);
        const byPrefix = nextListRequest(page, DONATIONS_REQUEST, (url) => url.searchParams.get('search') === prefix);
        await search.fill(prefix);
        await byPrefix;
        await expect(rowById(page, donation)).toBeVisible();

        const middle = last.slice(1, 4);
        const byMiddle = nextListRequest(page, DONATIONS_REQUEST, (url) => url.searchParams.get('search') === middle);
        await search.fill(middle);
        await byMiddle;
        await expect(rowById(page, donation)).toHaveCount(0);

        // The search term never goes in the address.
        expect(page.url()).not.toContain(`search=${middle}`);
    });
});

test.describe('Donations list money and dates', () => {
    test.use({locale: 'pt-BR'});
    useUsDateFormats();

    test('writes money and dates in the site format in a pt-BR browser', async ({page, admin, requestUtils}) => {
        expect(wp('eval', 'echo give_get_currency();'), 'This test expects a site in US dollars').toBe('USD');
        // A stored layout can reorder or hide the columns read below.
        await resetLayout(requestUtils, 'donations');

        const donationId = createDonation((await createCampaignWithForm(requestUtils)).formId);

        // The list request no longer carries the browser's language for the server to format with.
        const request = page.waitForRequest(DONATIONS_REQUEST);
        await visitList(page, admin, DONATIONS_LIST);
        expect(new URL((await request).url()).searchParams.has('locale')).toBe(false);

        const donation = rowById(page, donationId);
        await expectCell(donation, 'Amount', MONEY);
        await expectCell(donation, 'Date', DATE_TIME);
    });
});

test.describe('Donations list notices', () => {
    expectNoBrowserDialogs();

    const DISMISSALS = [
        'givewp_donations_recurring_recommendation_dismissed',
        'givewp_donations_fee_recovery_recommendation_dismissed',
        'givewp_donations_designated_funds_recommendation_dismissed',
    ];
    let restoreDismissals: () => void = () => undefined;

    test.beforeEach(() => {
        // Recommendation dismissals are stored per site; clear them so today's tip shows, and put them back after.
        restoreDismissals = saveOptions(...DISMISSALS);
        wp('option', 'delete', ...DISMISSALS);
    });

    test.afterEach(() => {
        restoreDismissals();
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
            await visitList(page, admin, DONATIONS_LIST);

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
});

test.describe('Donations list stat tiles', () => {
    expectNoBrowserDialogs();

    test.beforeEach(async ({requestUtils}) => {
        await resetLayout(requestUtils, 'donations');
    });

    test('tiles stay mounted, filter and show their pressed state', async ({page, admin, requestUtils}) => {
        const donationId = createDonation((await createCampaignWithForm(requestUtils)).formId);

        await visitList(page, admin, DONATIONS_LIST);

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

test.describe('Donations list on narrow screens', () => {
    expectNoBrowserDialogs();

    test('stacks as cards in order at 782 px and 375 px, with no page overflow', async ({
        page,
        admin,
        requestUtils,
    }) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        await expectStacksOnNarrowScreens(page, admin, requestUtils, {
            query: DONATIONS_LIST,
            key: 'donations',
            hasRecords: true,
        });

        // The stacking order, on the screen that has every part: title, test-mode switch, tiles,
        // notices, toolbar, then the cards.
        await page.setViewportSize({width: 782, height: 900});
        await admin.visitAdminPage('edit.php', DONATIONS_LIST);
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
});

test.describe('Donations list legacy view', () => {
    expectNoBrowserDialogs();

    test.afterEach(async ({requestUtils}) => {
        await requestUtils.rest({path: '/give-api/v2/admin/donations/view', params: {isLegacy: 0}});
    });

    test('Switch to Legacy View lives in the view menu', async ({page, admin, requestUtils}) => {
        await resetLayout(requestUtils, 'donations');
        await visitList(page, admin, DONATIONS_LIST);

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

/**
 * Opens the list and waits for its rows, so the filters are mounted by the time a test uses them.
 */
async function visitDonations(page: Page, admin?): Promise<void> {
    if (admin) {
        await admin.visitAdminPage('edit.php', DONATIONS_LIST);
    } else {
        await page.goto(`/wp-admin/edit.php?${DONATIONS_LIST}`);
    }
    await expect(page.getByRole('heading', {level: 1, name: 'Donations'})).toBeVisible({timeout: 15_000});
    await expect(listRows(page).first()).toBeVisible({timeout: 15_000});
}

/**
 * Sorts by a column through its header menu.
 */
async function sortBy(page: Page, column: string, direction: 'Sort ascending' | 'Sort descending'): Promise<void> {
    await page.locator('.dataviews-view-table thead th', {hasText: column}).getByRole('button').first().click();
    await page.getByRole('menuitemradio', {name: direction}).click();
    await page.keyboard.press('Escape');
}

/**
 * The `give-` parameters in the page's address.
 */
function giveParameters(page: Page): string[] {
    return Array.from(new URL(page.url()).searchParams.keys()).filter((key) => key.startsWith('give-'));
}

async function search(page: Page, term: string): Promise<void> {
    const searched = page.waitForRequest(
        (request) => LIST_REQUEST.test(request.url()) && request.url().includes(`search=${term}`)
    );
    await listTable(page).getByRole('searchbox').fill(term);
    await searched;
}

async function choosePageSize(page: Page, size: string): Promise<void> {
    await listTable(page).getByRole('button', {name: 'View options', exact: true}).click();
    await page.getByRole('radio', {name: size, exact: true}).click();
    await page.keyboard.press('Escape');
}
