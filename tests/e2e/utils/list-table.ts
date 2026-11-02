import type {Locator, Page} from '@playwright/test';
import {Admin, expect} from '@wordpress/e2e-test-utils-playwright';

/**
 * Helpers for the admin list screens, which render through `@wordpress/dataviews` with Give's own
 * search box and filter bar.
 *
 * DataViews gives rows no accessible name, so a row is found by its ID badge, the one cell every
 * list renders the same way.
 */

/**
 * The list area of the screen.
 */
export function listTable(page: Page): Locator {
    return page.locator('.givewp-list-table');
}

/**
 * Every row on the current page of the list.
 */
export function listRows(page: Page): Locator {
    return listTable(page).locator('.dataviews-view-table tbody tr');
}

/**
 * The row of record `id`.
 */
export function rowById(page: Page, id: number): Locator {
    return listRows(page).filter({has: page.locator('.idBadge', {hasText: new RegExp(`^${id}$`)})});
}

/**
 * The row whose cells contain `text`.
 */
export function rowWithText(page: Page, text: string): Locator {
    return listRows(page).filter({hasText: text});
}

/**
 * Opens a row's "Actions" menu and clicks `action` in it.
 */
export async function runRowAction(page: Page, row: Locator, action: string | RegExp): Promise<void> {
    await row.getByRole('button', {name: 'Actions', exact: true}).click();
    await page.getByRole('menuitem', {name: action}).click();
}

/**
 * Give's filter bar above the list. It takes the place of the stock DataViews filter row.
 */
export function filterBar(page: Page): Locator {
    return page.getByRole('group', {name: 'Filters', exact: true});
}

/**
 * The chip of a filter in the bar, found by the text it starts with ("Status", "Status: Pending").
 */
export function chip(page: Page, label: string): Locator {
    return filterBar(page).locator('.givewp-filter-chip__toggle', {hasText: new RegExp(`^${label}`)});
}

/**
 * The open popover of a filter chip.
 */
export function popover(page: Page): Locator {
    return page.locator('.givewp-filter-chip__content');
}

/**
 * Adds a secondary filter from "Add filter". Its popover opens at once.
 */
export async function addFilter(page: Page, label: string): Promise<void> {
    await filterBar(page).getByRole('button', {name: 'Add filter'}).click();
    await page.getByRole('menuitem', {name: label, exact: true}).click();
    await expect(popover(page)).toBeVisible();
}

/**
 * Applies a filter through the filter bar: opens its chip (or adds it from "Add filter"), types
 * `search` into the popover's search box when it has one (campaign, form and donor options are
 * searched on the server), and picks the option reading `label`.
 */
export async function applyFilter(
    page: Page,
    filter: string,
    search: string,
    label: string | RegExp = search
): Promise<void> {
    const filterChip = chip(page, filter);

    if (await filterChip.isVisible()) {
        await filterChip.click();
        await expect(popover(page)).toBeVisible();
    } else {
        await addFilter(page, filter);
    }

    const searchBox = popover(page).getByRole('searchbox');

    if (await searchBox.isVisible()) {
        await searchBox.fill(search);
    }

    const name = typeof label === 'string' ? {name: label, exact: true} : {name: label};
    const option = popover(page).getByRole('checkbox', name).or(popover(page).getByRole('radio', name));

    await option.first().check();
    await page.keyboard.press('Escape');
}

/**
 * The selection bar shown above the table while rows are selected. The footer has a second copy.
 */
export function selectionBar(page: Page): Locator {
    return listTable(page).locator('.givewp-list-table__bulk-actions .dataviews-bulk-actions-footer__container');
}

/**
 * Opens a list screen and checks it renders through DataViews, a table or its empty state, with no
 * script errors on the page. A failed list request shows in the empty state too, as an alert, so
 * the check fails on that alert.
 */
export async function expectListMounts(page: Page, admin: Admin, query: string, heading: string): Promise<void> {
    const pageErrors: string[] = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    await admin.visitAdminPage('edit.php', query);

    await expect(page.getByRole('heading', {level: 1, name: heading})).toBeVisible({timeout: 15_000});
    await expect(listTable(page).locator('.dataviews-view-table, .dataviews-no-results').first()).toBeVisible({
        timeout: 15_000,
    });
    await expect(listTable(page).getByRole('alert')).toHaveCount(0);
    expect(pageErrors).toEqual([]);
}
