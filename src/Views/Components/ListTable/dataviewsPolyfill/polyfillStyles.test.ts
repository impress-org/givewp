import {readFileSync} from 'fs';
import {join} from 'path';
import * as sass from 'sass';

// Sass cannot read the `@use`d file in this test environment, so the one variable it brings is set here.
const partial = readFileSync(join(__dirname, '_polyfill.scss'), 'utf8').replace(
    /^@use .*$/m,
    '$givewp-list-table-narrow: 782px;'
);
const css = sass
    .compileString(partial)
    .css.replace(/\/\*[\s\S]*?\*\//g, '');

const rules = Object.fromEntries(
    [...css.matchAll(/([^{}]+)\{([^{}]*)\}/g)].map(([, selector, body]) => [
        selector.trim().replace(/\s+/g, ' ').replace('.givewp-list-table .dataviews-view-table', ''),
        body.trim(),
    ])
);

describe('DataViews polyfill styles', () => {
    it('keep the edge cell and header padding of WordPress 7.0, also in compact density', () => {
        const header = 'thead th:has(.dataviews-view-table-header-button):not(:first-child, :last-child)';

        expect(rules[' tr > :first-child']).toBe('padding-inline: var(--givewp-spacing-6) var(--givewp-spacing-3);');
        expect(rules[' tr > :last-child']).toBe('padding-inline: var(--givewp-spacing-3) var(--givewp-spacing-6);');
        expect(rules[' tr > .dataviews-view-table__checkbox-column']).toBe('padding-inline-end: 0;');
        expect(rules[` ${header}`]).toBe('padding-inline: var(--givewp-spacing-1);');
        expect(rules['.has-compact-density tr > :last-child']).toBe('padding-inline-start: var(--givewp-spacing-2);');
        expect(rules[`.has-compact-density ${header}`]).toBe('padding-inline: 0;');
    });

    it('shares the free width between the columns', () => {
        expect(rules[' col.dataviews-view-table__col-primary']).toBe('width: 8%;');
        expect(rules[' col.dataviews-view-table__col-checkbox']).toBe('width: 0.1%;');
    });

    it('insets the footer, the states and the cards as much as the toolbar', () => {
        expect(css).toMatch(/\.dataviews-no-results \{\s*padding-inline: var\(--givewp-spacing-6\);/);
        expect(css).toMatch(/\.dataviews-view-grid-items \{\s*padding-inline: var\(--givewp-spacing-6\);/);
    });
});
