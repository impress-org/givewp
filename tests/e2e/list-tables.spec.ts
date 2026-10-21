import {expect, Page, test} from '@wordpress/e2e-test-utils-playwright';
import {createCampaignWithForm} from './utils/campaign';
import {createDonation} from './utils/donation';
import {applyFilter, listRows, listTable, rowById, selectionBar} from './utils/list-table';

/**
 * The behavior every admin list screen shares now that they render through `@wordpress/dataviews`:
 * the screen loads, row actions are reachable without hovering, filters show as chips and clear,
 * an empty result says why, sorting and page size reach the server, and selecting rows shows a count.
 *
 * The adapters between the screens' settings and DataViews are covered by Jest; this covers what
 * needs a browser. Donations is the screen with the most of each, so most checks run there.
 */
const SCREENS = {
    Campaigns: 'post_type=give_forms&page=give-campaigns',
    'Donation Forms': 'post_type=give_forms&page=give-forms',
    Donations: 'post_type=give_forms&page=give-payment-history',
    Donors: 'post_type=give_forms&page=give-donors',
    Subscriptions: 'post_type=give_forms&page=give-subscriptions',
    Events: 'post_type=give_forms&page=give-event-tickets',
};

test.describe('List tables', () => {
    for (const [heading, query] of Object.entries(SCREENS)) {
        test(`${heading} loads through the list engine without errors`, async ({page, admin}) => {
            const pageErrors: string[] = [];
            page.on('pageerror', (error) => pageErrors.push(error.message));

            const response = await page.goto(`/wp-admin/edit.php?${query}`);

            // Subscriptions needs Give Recurring and Events is a beta feature; neither is on in every environment.
            test.skip(response.status() >= 400, `${heading} is not available on this site`);

            await expect(page.getByRole('heading', {level: 1, name: heading})).toBeVisible({timeout: 15_000});
            await expect(listTable(page).locator('.dataviews-view-table, .dataviews-no-results').first()).toBeVisible({
                timeout: 15_000,
            });
            expect(pageErrors).toEqual([]);
        });
    }

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

        await applyFilter(page, 'Show trashed', 'Yes');
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

        const reset = page.waitForRequest(
            (request) => request.url().includes('admin/donations?') && !request.url().includes('status=')
        );
        await listTable(page).getByRole('button', {name: 'Reset', exact: true}).click();
        const request = await reset;

        await expect(chip(page, 'Status')).toHaveCount(0);
        expect(request.url()).toContain('sortColumn=amount');
        expect(request.url()).toContain('sortDirection=asc');
    });

    test('says no records match a search, and Clear filters clears it', async ({page, admin, requestUtils}) => {
        createDonation((await createCampaignWithForm(requestUtils)).formId);

        await visitDonations(page, admin);
        await listTable(page).getByRole('searchbox').fill('zzzzqqq');

        await expect(listTable(page)).toContainText('No donations match your search or filters.');
        await listTable(page).getByRole('button', {name: 'Clear filters'}).click();

        // Test mode is a filter too, so clearing filters turns it off; the test donations then leave the list.
        await expect(listTable(page).getByRole('searchbox')).toHaveValue('');
        await expect(page.getByRole('switch', {name: 'View Test Donations'})).toHaveAttribute('aria-checked', 'false');
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
        await listTable(page).getByRole('button', {name: 'View options'}).click();

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

async function visitDonations(page: Page, admin): Promise<void> {
    await admin.visitAdminPage('edit.php', SCREENS.Donations);
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

function chip(page: Page, filter: string) {
    return listTable(page).locator('.dataviews-filters__summary-chip', {hasText: new RegExp(`^${filter}`)});
}
