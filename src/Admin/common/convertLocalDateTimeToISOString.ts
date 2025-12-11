import {dateI18n, getDate} from '@wordpress/date';
/**
 * Convert a datetime-local string (e.g., "2025-10-21T15:30") to an ISO string.
 *
 * @since 4.13.0
 */
export default function convertLocalDateTimeToISOString(localDateTime: string): string {
    if (!localDateTime) {
        return '';
    }

    // Interpret the provided wall time in the WordPress site timezone and
    // return a site-local naive ISO-like string (no timezone). The server
    // interprets naive strings in wp_timezone().
    const date = getDate(localDateTime);
    if (isNaN(date.getTime())) {
        return '';
    }

    // Return RFC3339 with timezone offset to satisfy JSON Schema `date-time` validation
    // Example: 2025-10-21T22:00:00-04:00
    return dateI18n('Y-m-d\\TH:i:sP', date, undefined);
}
