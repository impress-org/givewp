import type {Locator, Page, Response} from '@playwright/test';
import {Admin, expect, RequestUtils, test} from '@wordpress/e2e-test-utils-playwright';
import {resetLayout} from './user';
import {saveOptions, wp} from './wp-cli';

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

/**
 * Fails the test when a browser dialog (alert, confirm, prompt) opens, and dismisses it so it cannot
 * block the run. Call it inside a `test.describe` whose tests should never open one.
 */
export function expectNoBrowserDialogs(): void {
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
}

/**
 * Opens a list screen and waits for its table or its empty state.
 */
export async function visitList(page: Page, admin: Admin, query: string): Promise<void> {
    await admin.visitAdminPage('edit.php', query);
    await expect(listTable(page).locator('.dataviews-view-table, .dataviews-no-results').first()).toBeVisible({
        timeout: 20_000,
    });
}

/**
 * The list request the page sends next whose URL passes `matches`.
 */
export function nextListRequest(
    page: Page,
    endpoint: RegExp,
    matches: (url: URL) => boolean = () => true
): Promise<Response> {
    return page.waitForResponse((response) => {
        const url = new URL(response.url());

        return endpoint.test(response.url()) && matches(url);
    });
}

/**
 * Every record of a v3 collection, page by page. The v3 donations route reports a wrong total, so
 * this reads until a page comes back short.
 */
export async function allRecords<T>(
    requestUtils: RequestUtils,
    path: string,
    params: Record<string, unknown>
): Promise<T[]> {
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
 * Turns the Donations list's test-mode switch to `isOn` when it is not there yet, and waits for the
 * list to reload.
 */
export async function setTestMode(page: Page, isOn: boolean): Promise<void> {
    const toggle = page.getByRole('switch');

    if ((await toggle.getAttribute('aria-checked')) === String(isOn)) {
        return;
    }

    const reloaded = nextListRequest(
        page,
        /give-api\/v2\/admin\/donations\?/,
        (url) => url.searchParams.get('testMode') === String(isOn)
    );
    await toggle.click();
    await reloaded;
}

let siteToday: string | undefined;

/**
 * `YYYY-MM-DD` of a day in the month before today, in the site's calendar. The seeded `createdAt` and
 * the list's day bounds are in the site's time zone, so the browser's clock must not choose the month.
 */
export function dayOfLastMonth(day: number): string {
    siteToday ??= wp('eval', 'echo wp_date("Y-m-d");');

    const [year, month] = siteToday.split('-').map(Number);
    // `month` is 1-based, so `month - 2` is last month in the 0-based Date API. Only the year and month are read.
    const date = new Date(Date.UTC(year, month - 2, 1));

    return [date.getUTCFullYear(), String(date.getUTCMonth() + 1).padStart(2, '0'), String(day).padStart(2, '0')].join(
        '-'
    );
}

/**
 * An amount in US dollars, as the lists write it: `$1,234.56`.
 */
export const MONEY = /^\$[\d,]+\.\d{2}$/;

/**
 * A date and time in the site's `F j, Y` and `g:i a` formats: `March 5, 2026 at 3:07 pm`.
 */
export const DATE_TIME = /^[A-Z][a-z]+ \d{1,2}, \d{4} at \d{1,2}:\d{2} (am|pm)$/;

/**
 * Sets the site's date and time formats to the ones `DATE_TIME` expects, for the tests in the
 * calling `test.describe`, and puts the old formats back after them.
 */
export function useUsDateFormats(): void {
    let restore: () => void = () => undefined;

    test.beforeAll(() => {
        restore = saveOptions('date_format', 'time_format');
        wp('option', 'update', 'date_format', 'F j, Y');
        wp('option', 'update', 'time_format', 'g:i a');
    });

    test.afterAll(() => {
        restore();
    });
}

/**
 * The text of the cell under the column headed `header` in `row`. DataViews gives cells no field
 * name, so the column is found by its position in the header row. The headers and the cell are read
 * in one step, because the columns can still change while the stored layout settles.
 */
export function cellText(row: Locator, header: string): Promise<string> {
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
export async function expectCell(row: Locator, header: string, pattern: RegExp): Promise<void> {
    await expect(row).toBeVisible();
    await expect.poll(() => cellText(row, header), {message: `${header} cell`}).toMatch(pattern);
}

/**
 * Whether the page scrolls sideways. Only the list's own scroll container may be wider than the window.
 */
export function pageOverflows(page: Page): Promise<boolean> {
    return page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
}

/**
 * The top edge of `locator`, in page pixels.
 */
export async function topOf(locator: Locator): Promise<number> {
    const box = await locator.boundingBox();

    expect(box, 'The element should be on the page').not.toBeNull();

    return box!.y;
}

/**
 * Checks that the last column of the list can be scrolled into view at 1024 px, with the "more to
 * the right" shadow shown until then, and that the page itself never scrolls sideways.
 */
export async function expectLastColumnReachable(page: Page, admin: Admin, query: string): Promise<void> {
    await page.setViewportSize({width: 1024, height: 900});
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

export type NarrowScreen = {
    /** The list screen's query string. */
    query: string;
    /** The screen's layout key, as the layouts route names it. */
    key: string;
    /** Whether the test made records for this screen, so cards must show. Without records it may show a blank slate. */
    hasRecords: boolean;
    /** Whether the whole page must fit the window, or only the list area. */
    pageFits?: boolean;
};

/**
 * Checks a list screen at 782 px and at 375 px: one card per record and no table, and nothing
 * wider than the window. With `pageFits: false` only the list area is checked, for a screen whose
 * page header is outside the list and may be wider.
 */
export async function expectStacksOnNarrowScreens(
    page: Page,
    admin: Admin,
    requestUtils: RequestUtils,
    {query, key, hasRecords, pageFits = true}: NarrowScreen
): Promise<void> {
    for (const width of [782, 375]) {
        const label = `${key} at ${width} px`;

        await page.setViewportSize({width, height: 900});
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
