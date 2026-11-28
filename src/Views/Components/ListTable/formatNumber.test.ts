import {_n} from '@wordpress/i18n';
import {browserLocale, formatNumber, insertNumbers} from './formatNumber';

const setBrowserLanguage = (language: string) =>
    jest.spyOn(window.navigator, 'language', 'get').mockReturnValue(language);

afterEach(() => jest.restoreAllMocks());

describe('formatNumber', () => {
    it.each([
        [0, '0'],
        [999, '999'],
        [1000, '1,000'],
        [1000000, '1,000,000'],
        [-1200, '-1,200'],
        [1200.9, '1,200'],
    ])('writes %p as %p in an English browser', (value, expected) => {
        setBrowserLanguage('en-US');

        expect(formatNumber(value)).toBe(expected);
    });

    it('follows the browser language, also for four digits where it would not group by default', () => {
        setBrowserLanguage('de-DE');
        expect(formatNumber(1200)).toBe('1.200');

        setBrowserLanguage('es-ES');
        expect(formatNumber(1200)).toBe('1.200');
    });

    it('still writes a number when the browser reports a tag Intl rejects', () => {
        setBrowserLanguage('en_US@posix');

        expect(formatNumber(1200)).toMatch(/^1.?200$/);
    });

    it('keeps a number that is not finite as text', () => {
        expect(formatNumber(Number.NaN)).toBe('NaN');
    });
});

describe('browserLocale', () => {
    it('is the browser language, which the requests send to the server', () => {
        setBrowserLanguage('pt-BR');

        expect(browserLocale()).toBe('pt-BR');
    });
});

describe('insertNumbers', () => {
    it('puts the formatted numbers into the translated message', () => {
        setBrowserLanguage('de-DE');

        expect(insertNumbers('%1$d of %2$d Items', {'%1$d': 30, '%2$d': 1200})).toBe('30 of 1.200 Items');
    });

    it('works on the singular and the plural message, which use the real number to pick the plural', () => {
        setBrowserLanguage('en-US');

        expect(insertNumbers(_n('%d Item', '%d Items', 1), {'%d': 1})).toBe('1 Item');
        expect(insertNumbers(_n('%d Item', '%d Items', 1200), {'%d': 1200})).toBe('1,200 Items');
    });

    it('leaves a message without the placeholder as it is', () => {
        expect(insertNumbers('No donations found', {'%1$d': 5})).toBe('No donations found');
    });
});
