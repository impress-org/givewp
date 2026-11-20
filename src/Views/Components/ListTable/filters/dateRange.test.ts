import {
    DATE_PRESETS,
    datePresetLabel,
    dateToken,
    dayBounds,
    isEndBeforeStart,
    parseDateToken,
    resolveDateRange,
} from './dateRange';
import {formatSiteDate, siteToday} from './siteDate';

describe('date range rules', () => {
    it.each([
        ['7', {start: '2026-10-14', end: '2026-10-20'}],
        ['30', {start: '2026-09-21', end: '2026-10-20'}],
        ['90', {start: '2026-07-23', end: '2026-10-20'}],
        ['this-month', {start: '2026-10-01', end: '2026-10-20'}],
        ['last-month', {start: '2026-09-01', end: '2026-09-30'}],
        ['this-year', {start: '2026-01-01', end: '2026-10-20'}],
        ['2026-09-01_2026-09-15', {start: '2026-09-01', end: '2026-09-15'}],
        ['2026-09-01_', {start: '2026-09-01'}],
        ['_2026-09-15', {end: '2026-09-15'}],
    ])('resolves %s', (token, days) => {
        const value = parseDateToken(token);

        expect(value).toBeDefined();
        expect(resolveDateRange(value!, '2026-10-20')).toEqual(days);
    });

    it('lists the presets in order', () =>
        expect(DATE_PRESETS).toEqual(['7', '30', '90', 'this-month', 'last-month', 'this-year']));

    it('last-month in January is December of last year', () =>
        expect(resolveDateRange({preset: 'last-month'}, '2027-01-10')).toEqual({
            start: '2026-12-01',
            end: '2026-12-31',
        }));

    it('this-month on the 1st is one day', () =>
        expect(resolveDateRange({preset: 'this-month'}, '2026-10-01')).toEqual({
            start: '2026-10-01',
            end: '2026-10-01',
        }));

    it('leap year: last-month in March 2028 ends 2028-02-29', () =>
        expect(resolveDateRange({preset: 'last-month'}, '2028-03-15')).toEqual({
            start: '2028-02-01',
            end: '2028-02-29',
        }));

    it.each(['31', '2026-02-30_', '2026-09-15_2026-09-01', '_', '2026-9-1_', '', 'a_b_c'])('rejects %p', (token) =>
        expect(parseDateToken(token)).toBeUndefined()
    );

    it.each(['7', 'this-year', '2026-09-01_2026-09-15', '2026-09-01_', '_2026-09-15', '2026-09-01_2026-09-01'])(
        'writes %s back as the same token',
        (token) => expect(dateToken(parseDateToken(token)!)).toBe(token)
    );

    it('adds whole-day bounds', () => {
        expect(dayBounds({start: '2026-09-01', end: '2026-09-15'})).toEqual({
            start: '2026-09-01T00:00:00',
            end: '2026-09-15T23:59:59',
        });
        expect(dayBounds({start: '2026-09-01'})).toEqual({start: '2026-09-01T00:00:00'});
        expect(dayBounds({end: '2026-09-15'})).toEqual({end: '2026-09-15T23:59:59'});
    });

    it('knows when the end is before the start', () => {
        expect(isEndBeforeStart('2026-09-15', '2026-09-01')).toBe(true);
        expect(isEndBeforeStart('2026-09-01', '2026-09-01')).toBe(false);
        expect(isEndBeforeStart('2026-09-01', undefined)).toBe(false);
        expect(isEndBeforeStart(undefined, '2026-09-01')).toBe(false);
    });

    it.each([
        ['7', 'Last 7 days'],
        ['30', 'Last 30 days'],
        ['90', 'Last 90 days'],
        ['this-month', 'This month'],
        ['last-month', 'Last month'],
        ['this-year', 'This year'],
    ] as const)('labels %s', (preset, label) => expect(datePresetLabel(preset)).toBe(label));
});

describe('site date', () => {
    it('formats a day in the site date format', () => expect(formatSiteDate('2026-09-01')).toBe('September 1, 2026'));

    it('gives today as a day', () => expect(siteToday()).toMatch(/^\d{4}-\d{2}-\d{2}$/));
});
