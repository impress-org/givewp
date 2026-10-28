import {expect, Page, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation} from './utils/donation';
import {applyFilter, listRows, listTable} from './utils/list-table';
import {deleteUser, resetLayout, signInNewUser} from './utils/user';

/**
 * The list view kept in the address: what is shown (page, page size, sort, filters, test mode) is in
 * `give-` parameters, so reload, back and forward and a shared link all land on the same view. The
 * search term stays with the tab's history and never reaches the address.
 *
 * Parsing and writing the parameters are covered by Jest; this checks the browser does what the
 * history helper asks of it, on the real Donations screen, which has the most filters.
 */
const DONATIONS_LIST = 'post_type=give_forms&page=give-payment-history';
const LIST_REQUEST = /admin\/donations\?/;

test.describe('List view in the address', () => {
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

        // DataViews keeps the filter bar folded on load unless a filter is primary; the button opens it.
        await listTable(page).getByRole('button', {name: 'Filter', exact: true}).click();
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

    test('never writes the search to the address, and a new page opens without it', async ({
        page,
        admin,
        context,
    }) => {
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
        // WordPress core's admin canonical-URL script runs `history.replaceState(null, …)` on every admin
        // page load, before the list reads the tab's history entry, so the term is lost on reload.
        test.fail(true, 'The admin canonical-URL script clears history.state on load');

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

        const toggle = page.getByRole('switch', {name: 'View Test Donations'});
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
 * Opens the Donations list and waits for its rows.
 */
async function visitDonations(page: Page, admin): Promise<void> {
    await admin.visitAdminPage('edit.php', DONATIONS_LIST);
    await expect(page.getByRole('heading', {level: 1, name: 'Donations'})).toBeVisible({timeout: 15_000});
    await expect(listRows(page).first()).toBeVisible({timeout: 15_000});
}

/**
 * The `give-` parameters in the page's address.
 */
function giveParameters(page: Page): string[] {
    return Array.from(new URL(page.url()).searchParams.keys()).filter((key) => key.startsWith('give-'));
}

async function search(page: Page, term: string): Promise<void> {
    const searched = page.waitForRequest((request) => LIST_REQUEST.test(request.url()) && request.url().includes(`search=${term}`));
    await listTable(page).getByRole('searchbox').fill(term);
    await searched;
}

async function choosePageSize(page: Page, size: string): Promise<void> {
    await listTable(page).getByRole('button', {name: 'View options'}).click();
    await page.getByRole('radio', {name: size, exact: true}).click();
    await page.keyboard.press('Escape');
}

async function sortBy(page: Page, column: string, direction: 'Sort ascending' | 'Sort descending'): Promise<void> {
    await page.locator('.dataviews-view-table thead th', {hasText: column}).getByRole('button').first().click();
    await page.getByRole('menuitemradio', {name: direction}).click();
    await page.keyboard.press('Escape');
}

function chip(page: Page, filter: string) {
    return listTable(page).locator('.dataviews-filters__summary-chip', {hasText: new RegExp(`^${filter}`)});
}
