import {readdirSync, readFileSync, statSync} from 'fs';
import {join, relative, resolve} from 'path';

const SHARED_DIR = resolve(__dirname, '..');

/**
 * Every stylesheet in the shared list table folder.
 */
function stylesheets(dir: string): string[] {
    return readdirSync(dir).flatMap((name) => {
        const path = join(dir, name);

        if (statSync(path).isDirectory()) {
            return stylesheets(path);
        }

        return name.endsWith('.scss') ? [path] : [];
    });
}

describe('list table styles', () => {
    // The token is 0.5rem (8px): too small for text anyone has to read.
    it('never set text in the paragraph-sm size', () => {
        const offenders = stylesheets(SHARED_DIR)
            .filter((path) => readFileSync(path, 'utf8').includes('--givewp-font-size-paragraph-sm'))
            .map((path) => relative(SHARED_DIR, path));

        expect(offenders).toEqual([]);
    });

    it('size the search label for the label the installed WordPress renders', () => {
        const source = readFileSync(join(SHARED_DIR, 'ListTableSearch', 'styles.scss'), 'utf8');

        expect(source).toContain('.components-input-control__label');
    });

    it('show the search label in sentence case, not the stock uppercase', () => {
        const source = readFileSync(join(SHARED_DIR, 'ListTableSearch', 'styles.scss'), 'utf8');
        const labelRule = source.slice(
            source.indexOf('.components-input-control__label.components-input-control__label')
        );

        expect(labelRule.slice(0, labelRule.indexOf('}'))).toContain('text-transform: none');
    });

    it('draws the search edge on the shared box, so the icon and the input are one box', () => {
        const source = readFileSync(join(SHARED_DIR, 'ListTableSearch', 'styles.scss'), 'utf8');
        const input = source.slice(source.indexOf("input[type='search'] {"));

        expect(source).toContain('.components-input-control__backdrop');
        expect(source).toContain('border-color: var(--givewp-list-table-control-edge)');
        expect(input.slice(0, input.indexOf('\n    }\n'))).not.toMatch(/border(?!-)/);
    });

    it('use the shared narrow breakpoint, never a literal width', () => {
        const sources = ['ListTableSearch', 'FilterChip'].map((folder) =>
            readFileSync(join(SHARED_DIR, folder, 'styles.scss'), 'utf8')
        );

        sources.forEach((source) => {
            expect(source).toContain('$givewp-list-table-narrow');
            expect(source).not.toContain('782px');
            expect(source).toContain('2.5rem');
        });
    });
});
