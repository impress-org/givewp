import {readFileSync} from 'fs';
import {join} from 'path';

const source = readFileSync(join(__dirname, 'styles.scss'), 'utf8');
const block = (selector: string) => {
    const start = source.indexOf(`\n    ${selector} {`);

    return source.slice(start, source.indexOf('}', start));
};

// jsdom cannot paint, so these checks keep the stock WordPress look in the source.
describe('Balloon styles', () => {
    const root = source.slice(source.indexOf('.givewp-balloon {'), source.indexOf('&__close'));

    it('is a white box with the stock radius, a 1px neutral edge and a small shadow', () => {
        expect(root).toContain('border: 1px solid var(--givewp-list-table-hairline)');
        expect(root).toContain('border-radius: var(--givewp-rounded-2)');
        expect(root).toContain('background-color: var(--givewp-shades-white)');
        expect(root).toContain('box-shadow: var(--givewp-shadow-md)');
    });

    it('brings its own tokens, since it also shows outside the list page roots', () => {
        expect(root).toContain('@include givewp-list-table-tokens');
    });

    it('sets no position, so each screen keeps its own placement', () => {
        expect(root).not.toMatch(/\b(position|inset[\w-]*|top|right|bottom|left|z-index|max-inline-size):/);
    });

    it('uses 13px body text and a 14px semibold title', () => {
        expect(root).toContain('font-size: 0.8125rem');
        expect(block('&__title')).toContain('font-size: 0.875rem');
        expect(block('&__title')).toContain('font-weight: 600');
    });

    it('uses logical properties only', () => {
        expect(source).not.toMatch(/\b(margin|padding|border)-(left|right|top|bottom)\b/);
        expect(source).not.toMatch(/^\s*(top|right|bottom|left):/m);
        expect(source).not.toMatch(/text-align:\s*(left|right)/);
    });
});
