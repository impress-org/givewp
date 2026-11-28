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
            // Below 783 px the list shows cards, not a table, so wait for the bar instead of a table.
            await admin.visitAdminPage('edit.php', DONATIONS_LIST);
            await expect(filterBar(page)).toBeVisible({timeout: 20_000});

            await applyFilter(page, 'Status', 'Completed');
            await addFilter(page, 'Donation date');
            await page.keyboard.press('Escape');

            await expect(chip(page, 'Status')).toContainText('Completed');

            const group = filterBar(page);

            // Read both boxes and the direction in one go, so the check is made on one layout.
            const {add, lastChip, isRtl} = await group.evaluate((element) => {
                const box = (target: Element | null) => {
                    const {left, right, top, height} = target!.getBoundingClientRect();

                    return {left, right, centerY: top + height / 2, height};
                };
                const items = element.querySelectorAll('.givewp-list-table-filters__item');

                return {
                    add: box(element.querySelector('.givewp-list-table-filters__add')),
                    lastChip: box(items[items.length - 1]),
                    isRtl: getComputedStyle(element).direction === 'rtl',
                };
            });
            // Rows differ by a whole control height, so half a height tells "same line" from "next line".
            const halfLine = Math.min(add.height, lastChip.height) / 2;

            // "Add filter" is on the last chip's line or below it (block axis, so the direction does not matter) ...
            expect(add.centerY).toBeGreaterThanOrEqual(lastChip.centerY - halfLine);
            // ... and when they share a line it comes after the chip, on the inline-end side.
            if (Math.abs(add.centerY - lastChip.centerY) < halfLine) {
                if (isRtl) {
                    expect(add.right).toBeLessThanOrEqual(lastChip.left + 1);
                } else {
                    expect(add.left).toBeGreaterThanOrEqual(lastChip.right - 1);
                }
            }

            // The group never overflows its own box, and the page never scrolls sideways.
            const fits = await group.evaluate((element) => element.scrollWidth <= element.clientWidth);
            expect(fits).toBe(true);
            expect(await pageOverflows(page)).toBe(false);
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
