import {periodFilterGroup} from './periodFilterGroup';

describe('periodFilterGroup', () => {
    it('keeps the stable day counts as values', () => {
        expect(periodFilterGroup().options.map((option) => option.value)).toEqual(['90', '30', '7']);
    });

    it('sends the ISO date that many days before the request time', () => {
        // July has no daylight-saving change in common timezones, so local calendar days and 24 hours agree.
        const now = new Date('2026-07-31T12:00:00.000Z');

        expect(periodFilterGroup().toApiValue?.('30', now)).toBe('2026-07-01T12:00:00.000Z');
    });
});
