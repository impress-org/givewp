import {dateI18n} from '@wordpress/date';
/**
 * @since 4.10.0
 */
export function formatTimestamp(timestamp: string | null | undefined, useComma: boolean = false): string {
    // Handle null, undefined, or empty string
    if (!timestamp) {
        return '—';
    }

    // Normalize naive timestamps (no timezone) as UTC to avoid shifts
    const hasTimezone = /[zZ]|[+-]\d{2}:?\d{2}$/.test(timestamp);
    const normalized = hasTimezone ? timestamp : `${timestamp}Z`;
    const date = new Date(normalized);

    // Check if the date is valid
    if (isNaN(date.getTime())) {
        return '—';
    }

    const format = useComma ? 'jS F Y, g:i a' : 'jS F Y • g:i a';
    return dateI18n(format, date, undefined);
}

