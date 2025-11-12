import {humanTimeDiff, getDate} from '@wordpress/date';

/**
 * Returns a relative time string for a given date (e.g. "Today" or "2 days ago")
 *
 * @since 4.13.0 updated to use the @wordpress/date functions
 * @since 4.10.0
 */
export function getRelativeTimeString(date: Date): string {
    const now = getDate(new Date().toISOString());

    return humanTimeDiff(date, now);
}
