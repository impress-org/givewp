import {readFileSync} from 'fs';
import {resolve} from 'path';

const read = (...path: string[]) => readFileSync(resolve(__dirname, ...path), 'utf8');

// Every pill-like badge a list cell can show, with the stylesheet and the block that styles it.
const PILLS = [
    {name: 'type badge (.badge)', source: read('styles.scss'), opener: '.badge {'},
    {
        name: 'Default form pill (.defaultFormPill)',
        source: read(
            '..',
            '..',
            '..',
            '..',
            'DonationForms',
            'V2',
            'resources',
            'components',
            'DonationFormsListTable.module.scss'
        ),
        opener: '.defaultFormPill {',
    },
];

// Only the pill block: from its opener to the closing brace (brace matching).
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

describe('cell pills', () => {
    it.each(PILLS)('$name is square with the stock 2px radius', ({source, opener}) => {
        const block = sliceBlock(source, opener);

        expect(block).not.toBe('');
        expect(block).toContain('border-radius: 2px;');
        expect(block).not.toMatch(/rounded-(full|12)|9999px|1\.5rem/);
    });
});
