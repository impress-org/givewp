import {setSettings, getSettings} from '@wordpress/date';
import {formatSiteDate, siteToday} from './siteDate';

describe('siteDate', () => {
    const original = getSettings();

    const useZone = (offset: number) =>
        setSettings({...original, timezone: {offset, offsetFormatted: String(offset), string: ''}});

    afterEach(() => {
        jest.useRealTimers();
        setSettings(original);
    });

    // 23:30 UTC on 14 March: it is already 15 March at UTC+13 and still 14 March at UTC-11.
    it('reads today in the site zone, not the browser zone', () => {
        jest.useFakeTimers().setSystemTime(new Date('2026-03-14T23:30:00Z'));

        useZone(13);
        expect(siteToday()).toBe('2026-03-15');

        useZone(-11);
        expect(siteToday()).toBe('2026-03-14');
    });

    it('keeps the same calendar day when it formats a day', () => {
        useZone(-11);

        expect(formatSiteDate('2026-03-15')).toContain('15');
    });
});
