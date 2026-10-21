import {expect, Locator, Page} from '@wordpress/e2e-test-utils-playwright';

/**
 * Helpers for the admin list screens, which render through `@wordpress/dataviews`.
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
 * Applies a filter through the DataViews toolbar: picks `filter` in "Add filter" (or opens its
 * chip when it is already applied), searches for `search` when the filter has a search box, and picks
 * the option reading `label`.
 */
export async function applyFilter(
    page: Page,
    filter: string,
    search: string,
    label: string | RegExp = search
): Promise<void> {
    const list = listTable(page);
    const chip = list.locator('.dataviews-filters__summary-chip', {hasText: new RegExp(`^${filter}( |$)`)});
    const addFilter = list.getByRole('button', {name: 'Add filter', exact: true});
    const menuItem = page.getByRole('menuitem', {name: filter, exact: true});

    if (!(await chip.isVisible()) && !(await addFilter.isVisible())) {
        // With no filter applied, "Filter" opens the "Add filter" menu itself; otherwise it shows the bar.
        await list.getByRole('button', {name: 'Filter', exact: true}).click();
        await expect(menuItem.or(addFilter).first()).toBeVisible();
    }

    if (await chip.isVisible()) {
        await chip.click();
    } else {
        if (!(await menuItem.isVisible())) {
            await addFilter.click();
        }
        await menuItem.click();
    }

    // A long list of options comes with a search box; a short one is shown whole.
    const popover = page.locator('.dataviews-filters__summary-popover');
    const option = popover.getByRole('option', {name: label}).or(popover.getByRole('radio', {name: label}));
    await expect(option.or(popover.getByRole('combobox')).first()).toBeVisible();

    if (await popover.getByRole('combobox').isVisible()) {
        await popover.getByRole('combobox').fill(search);
    }

    await option.first().click();
    await page.keyboard.press('Escape');
}

/**
 * The selection bar shown above the table while rows are selected. The footer has a second copy.
 */
export function selectionBar(page: Page): Locator {
    return listTable(page).locator('.givewp-list-table__bulk-actions .dataviews-bulk-actions-footer__container');
}
