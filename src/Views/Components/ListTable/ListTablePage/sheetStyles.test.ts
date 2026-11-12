import {readFileSync} from 'fs';
import {join} from 'path';

const LIST_TABLE_DIR = join(__dirname, '..');
const read = (...path: string[]) => readFileSync(join(LIST_TABLE_DIR, ...path), 'utf8');

/**
 * The text of the first rule block that opens right after `selector`, braces matched.
 */
function block(source: string, selector: string): string {
    const start = source.indexOf(`\n${selector} {`);

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

describe('list sheet styles', () => {
    const page = read('ListTablePage', 'ListTablePage.module.scss');

    it('draws one bordered, rounded sheet that clips its corners without a scroll container', () => {
        const sheet = block(page, '.sheet');

        expect(sheet).toContain('border: 1px solid var(--givewp-list-table-hairline)');
        expect(sheet).toContain('border-radius: var(--givewp-rounded-8)');
        expect(sheet).toContain('overflow: clip');
    });

    it('lays the tile band full width at the top of the sheet, with a hairline under it', () => {
        const band = block(read('StatTiles', 'styles.scss'), '.givewp-stat-tiles');

        expect(band).toContain('border-block-end: 1px solid var(--givewp-list-table-hairline)');
        expect(band).not.toContain('margin-inline');
        expect(band).not.toMatch(/(^|\s)border:/);
        expect(band).not.toContain('background');
    });

    it('fills the viewport under the admin bar and above the WordPress footer, so a short list does not scroll', () => {
        expect(block(page, '.page')).toContain(
            'min-block-size: calc(100vh - var(--wp-admin--admin-bar--height, 32px) - $givewp-list-table-wp-footer-space)'
        );
        expect(page).toMatch(/\$givewp-list-table-wp-footer-space:\s*65px;/);
        expect(page.match(/65px/g)).toHaveLength(1);
    });

    it('paints the admin wrapper the page color on the full page only', () => {
        const rule = block(page, '    .post-type-give_forms #wpbody:has(.givewp-list-table-page)');

        expect(rule).toContain('background-color: $givewp-list-table-page');
        expect(page).not.toContain('padding-block-end');
        expect(read('ListTablePage', 'index.tsx')).toContain(
            "contentMode ? styles.pageEmbedded : 'givewp-list-table-page'"
        );
    });

    it('spaces the list content 12px from the band and the notices on the full page, with no toolbar padding above', () => {
        const content = block(page, '.sheet > .pageContent');

        expect(content).toContain('gap: var(--givewp-spacing-3)');
        expect(content).toContain('padding-block-start: var(--givewp-spacing-3)');
        expect(content).toMatch(/\.givewp-list-table__toolbar\)\s*\{\s*padding-block-start: 0;/);
    });

    it('does not fill the viewport when embedded in another page', () => {
        expect(block(page, '.pageEmbedded')).toContain('min-block-size: auto');
    });
});
