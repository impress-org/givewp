import {date, getSettings, gmdateI18n} from '@wordpress/date';

/**
 * Today in the site's time zone, as `YYYY-MM-DD`. Never the browser's zone.
 *
 * @since TBD
 */
export function siteToday(): string {
    // The typings ask for all three arguments; `undefined` means now, in the site's zone.
    return date('Y-m-d', undefined, undefined);
}

/**
 * A `YYYY-MM-DD` day in the site's date format. The day has no zone, so it is read as UTC and shown
 * as UTC to keep the same calendar day.
 *
 * @since TBD
 */
export function formatSiteDate(ymd: string): string {
    return gmdateI18n(getSettings().formats.date, `${ymd}T00:00:00Z`);
}
