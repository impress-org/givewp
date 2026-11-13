import {readFileSync} from 'fs';
import {join} from 'path';
import * as sass from 'sass';

const partial = readFileSync(join(__dirname, '_wp-look.scss'), 'utf8');
const compiled = sass.compileString(partial, {loadPaths: [join(__dirname, '..')]}).css;

const selectors = [...compiled.replace(/\/\*[\s\S]*?\*\//g, '').matchAll(/([^{}]+)\{/g)]
    .flatMap(([, list]) => list.split(/,(?![^(]*\))/))
    .map((selector) => selector.trim())
    .filter(Boolean);

// A selector's simple parts, split at combinators, for example `td > a:hover` gives `td`, `a:hover`.
const simpleSelectors = (selector: string) => selector.split(/[\s>+~]+/).filter(Boolean);

describe('the A+ table rules', () => {
    it('compile to at least one selector', () => {
        expect(selectors.length).toBeGreaterThan(0);
    });

    it('never target the footer, the pager or the generic WordPress components', () => {
        const forbidden = [/footer/i, /pagination/i, /\.components-/];
        for (const selector of selectors) {
            for (const pattern of forbidden) {
                expect({selector, hit: pattern.test(selector)}).toEqual({selector, hit: false});
            }
        }
    });

    it('never target a bare button, select or input', () => {
        for (const selector of selectors) {
            const bare = simpleSelectors(selector).filter((part) => /^(button|select|input)(?![\w-])/.test(part));
            expect({selector, bare}).toEqual({selector, bare: []});
        }
    });

    it('stay under .givewp-list-table .dataviews-view-table', () => {
        for (const selector of selectors) {
            expect({selector, scoped: /^\.givewp-list-table \.dataviews-view-table(?![\w-])/.test(selector)}).toEqual({
                selector,
                scoped: true,
            });
        }
    });
});
