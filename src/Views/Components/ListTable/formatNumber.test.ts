import {_n} from '@wordpress/i18n';
import {formatNumber, insertNumbers} from './formatNumber';

const setSeparator = (thousandsSep: string) => {
    window.GiveListTableNumbers = {thousandsSep, decimalPoint: '.'};
};

afterEach(() => {
    delete window.GiveListTableNumbers;
});

describe('formatNumber', () => {
    it.each([
        [0, '0'],
        [1, '1'],
        [999, '999'],
        [1000, '1,000'],
        [100000, '100,000'],
        [1000000, '1,000,000'],
        [33334, '33,334'],
        [-1200, '-1,200'],
        [1200.9, '1,200'],
    ])('writes %p as %p when the page prints no separator', (value, expected) => {
        expect(formatNumber(value)).toBe(expected);
    });

    it('uses the separator of the locale (de_DE)', () => {
        setSeparator('.');

        expect(formatNumber(1200)).toBe('1.200');
        expect(formatNumber(1000000)).toBe('1.000.000');
    });

    it('accepts a space (fr_FR) or a longer separator', () => {
        setSeparator(' ');
        expect(formatNumber(1234567)).toBe('1 234 567');

        setSeparator(' - ');
        expect(formatNumber(12345)).toBe('12 - 345');
    });

    it('leaves a separator with dollar signs as it is', () => {
        setSeparator('$&');

        expect(formatNumber(1200)).toBe('1$&200');
    });

    it('keeps a number that is not finite as text', () => {
        expect(formatNumber(Number.NaN)).toBe('NaN');
    });
});

describe('insertNumbers', () => {
    it('puts the formatted numbers into the translated message', () => {
        setSeparator('.');

        expect(insertNumbers('%1$d of %2$d Items', {'%1$d': 30, '%2$d': 1200})).toBe('30 of 1.200 Items');
    });

    it('works on the singular and the plural message, which use the real number to pick the plural', () => {
        expect(insertNumbers(_n('%d Item', '%d Items', 1), {'%d': 1})).toBe('1 Item');
        expect(insertNumbers(_n('%d Item', '%d Items', 1200), {'%d': 1200})).toBe('1,200 Items');
    });

    it('leaves a message without the placeholder as it is', () => {
        expect(insertNumbers('No donations found', {'%1$d': 5})).toBe('No donations found');
    });
});
