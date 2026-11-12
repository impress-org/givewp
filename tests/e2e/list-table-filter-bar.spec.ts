import {expect, test} from '@wordpress/e2e-test-utils-playwright';
import {addFilter, applyFilter, chip, filterBar, pageOverflows, visitList} from './utils/list-table';

/**
 * The filter bar above a list: its chips wrap at narrow widths, "Add filter" follows the last chip, and
 * the page never scrolls sideways. The Donations list is used because it has the most filters.
 */
const DONATIONS_LIST = 'post_type=give_forms&page=give-payment-history';

test.describe('Filter bar layout', () => {
    for (const width of [1024, 782, 390]) {
        test(`filter bar wraps without a sideways page scroll at ${width}px`, async ({page, admin}) => {
            await page.setViewportSize({width, height: 900});
            await visitList(page, admin, DONATIONS_LIST);

            await applyFilter(page, 'Status', 'Completed');
            await addFilter(page, 'Donation date');
            await page.keyboard.press('Escape');

            const bar = filterBar(page);
            await expect(chip(page, 'Status')).toContainText('Completed');
            expect(await pageOverflows(page)).toBe(false);

            const add = await bar.locator('.givewp-list-table-filters__add').boundingBox();
            const lastChip = await bar.locator('.givewp-list-table-filters__item').last().boundingBox();

            // "Add filter" is on or below the last chip's line.
            expect(add!.y + add!.height).toBeGreaterThanOrEqual(lastChip!.y);
        });
    }

    test('the remove button is a 24 px target named after its filter', async ({page, admin}) => {
        await visitList(page, admin, DONATIONS_LIST);
        await applyFilter(page, 'Status', 'Completed');

        const remove = filterBar(page).getByRole('button', {name: /^Reset filter: Status/});
        const box = await remove.boundingBox();

        expect(box!.width).toBeGreaterThanOrEqual(24);
        expect(box!.height).toBeGreaterThanOrEqual(24);
    });
});
