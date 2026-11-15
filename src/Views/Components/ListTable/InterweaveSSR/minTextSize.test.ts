import {readFileSync} from 'fs';
import {resolve} from 'path';

const read = (...path: string[]) => readFileSync(resolve(__dirname, ...path), 'utf8');

// Only the block from its opener to the closing brace (brace matching).
const sliceBlock = (source: string, opener: string) => {
    const start = source.indexOf(opener);
    let depth = 0;
    for (let i = source.indexOf('{', start); i < source.length; i++) {
        depth += source[i] === '{' ? 1 : source[i] === '}' ? -1 : 0;
        if (depth === 0) {
            return source.slice(start, i + 1);
        }
    }
    return '';
};

const cellStyles = read('styles.scss');
const formsStyles = read(
    '..',
    '..',
    '..',
    '..',
    'DonationForms',
    'V2',
    'resources',
    'components',
    'DonationFormsListTable.module.scss'
);

// The list pages show no text under 13 px (0.8125rem), outside the stock DataViews footer.
describe('text size in the list cells and the search label', () => {
    it.each([
        ['type badge text', sliceBlock(cellStyles, '.badge {')],
        ['goal progress status', sliceBlock(cellStyles, '&__status {')],
        ['Default form pill', sliceBlock(formsStyles, '.defaultFormPill {')],
        [
            'search label',
            sliceBlock(
                read('..', 'ListTableSearch', 'styles.scss'),
                '& &__control .components-base-control__label,'
            ),
        ],
    ])('%s is 13px', (_name, block) => {
        expect(block).not.toBe('');
        expect(block).toContain('font-size: 0.8125rem');
        expect(block).not.toMatch(/font-size: (0\.75rem|var\(--givewp-font-size-paragraph-md\))/);
    });
});
