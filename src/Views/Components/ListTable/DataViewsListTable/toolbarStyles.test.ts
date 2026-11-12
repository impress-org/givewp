import {readFileSync} from 'fs';
import {join} from 'path';

const LIST_TABLE_DIR = join(__dirname, '..');
const read = (...path: string[]) => readFileSync(join(LIST_TABLE_DIR, ...path), 'utf8');

/**
 * The text of the first rule block that opens right after `selector`, braces matched.
 */
function block(source: string, selector: string): string {
    const start = source.indexOf(`${selector} {`);

    if (start < 0) {
        return '';
    }

    const open = source.indexOf('{', start);
    let depth = 0;

    for (let index = open; index < source.length; index++) {
        depth += source[index] === '{' ? 1 : source[index] === '}' ? -1 : 0;

        if (depth === 0) {
            return source.slice(open + 1, index);
        }
    }

    return '';
}

/**
 * Whether a block sets `block-size` itself (not only `min-block-size`) to `value`.
 */
const setsBlockSize = (source: string, value: string) =>
    new RegExp(`(?<![-\\w])block-size: ${value.replace('.', '\\.')};`).test(source);

describe('list toolbar styles', () => {
    const table = read('DataViewsListTable', 'styles.scss');

    it('lays the search box, the filter bar and the view options in one wrapping row', () => {
        const row = block(table, '&__toolbar-row');

        expect(row).toContain('display: flex');
        expect(row).toContain('flex-wrap: wrap');
        // The chips line up with the input: the row keeps the label's space above all of them.
        expect(row).toContain('align-items: flex-start');
        expect(block(row, '&--labeled')).toContain('padding-block-start: $givewp-list-table-search-label-block');
    });

    it('places the search label out of the flow, with a line and a gap of our own', () => {
        const search = read('ListTableSearch', 'styles.scss');
        const label = block(search, '& &__control .components-input-control__label.components-input-control__label');

        expect(search).toContain("@use './label'");
        expect(label).toContain('position: absolute');
        expect(label).toContain('line-height: $givewp-list-table-search-label-line');
        expect(block(search, '& &__control .components-input-base')).toContain('gap: 0');
    });

    it('keeps no rule for the old separate search and filter rows', () => {
        expect(table).not.toContain('&__filter-row');
        expect(table).not.toContain('.dataviews__search');
        expect(table).not.toContain('.dataviews__view-actions');
    });

    it('sizes the search input as the design: 360 px wide, 32 px tall above the narrow width', () => {
        const search = read('ListTableSearch', 'styles.scss');

        expect(block(search, '.givewp-list-table-search')).toContain('inline-size: 22.5rem');
        // Four classes: the stock input height is set by a triple-class rule.
        expect(setsBlockSize(block(search, "& &__control input[type='search']"), '2rem')).toBe(true);
    });

    it('makes the chips, "Add filter" and Reset 32 px tall, as tall as the input beside them', () => {
        const filters = read('ListTableFilters', 'styles.scss');

        const chip = read('FilterChip', 'styles.scss');

        expect(setsBlockSize(block(chip, '&__toggle.components-button'), '1.875rem')).toBe(true);
        expect(setsBlockSize(block(chip, '&__remove.components-button'), '1.5rem')).toBe(true);
        expect(filters).toContain('.givewp-list-table-filters__add .components-button,');
        expect(setsBlockSize(block(filters, '.givewp-list-table-filters__reset.components-button'), '2rem')).toBe(true);
    });

    it('leaves the space under the toolbar to the toolbar, not to the filter bar', () => {
        expect(read('ListTableFilters', 'styles.scss')).not.toContain('padding-block');
    });
});
