import {dateI18n, getDate, getSettings} from '@wordpress/date';
/**
 * Format the timestamp using the WordPress site settings for timezone and format.
 *
 * @since 4.13.0 updated to use the @wordpress/date functions
 * @since 4.10.0
 */
export function formatTimestamp(timestamp: string | null | undefined, includeTime: boolean = true): string {
    // Handle null, undefined, or empty string
    if (!timestamp) {
        return '—';
    }

    // Parse timestamps using WordPress site timezone. Works with naive and offset/Z strings.
    const date = getDate(timestamp);

    // Check if the date is valid
    if (isNaN(date.getTime())) {
        return '—';
    }

    const {formats} = getSettings();
    const datePart = dateI18n(formats.date || 'F j, Y', date, undefined);
    if (includeTime) {
        const timePart = dateI18n(formats.time || 'g:i a', date, undefined);
        return `${datePart} ${timePart}`;
    }

    return datePart;
}

