/**
 * WordPress dependencies
 */
import {dateI18n, getDate} from '@wordpress/date';
/**
 * @since 4.4.0
 */
export function amountFormatter(currency: Intl.NumberFormatOptions['currency'], options?: Intl.NumberFormatOptions): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options
    });
}

/**
 * @since 4.6.0
 */
export function formatDateTimeLocal(dateString: string) {
    if (!dateString) return '';

    // Treat server-provided naive strings (without timezone) as UTC to avoid shifts
    // Example server string: "2025-10-21T22:30:00" (no timezone) => interpret as UTC
    const hasTimezone = /[zZ]|[+-]\d{2}:?\d{2}$/.test(dateString);
    const normalized = hasTimezone ? dateString : `${dateString}Z`;

    const dateObj = new Date(normalized);
    const timestamp = dateObj.getTime();
    if (isNaN(timestamp)) return '';

    return dateI18n('Y-m-d\\TH:i', dateObj, undefined);
}


/**
 * Convert a datetime-local string (e.g., "2025-10-21T15:30") to an ISO string.
 * @unreleased
 */
export function toISOStringFromLocalDateTime(localDateTime: string): string {
    if (!localDateTime) return '';

    // Interpret the provided wall time in the WordPress site timezone and convert to UTC ISO.
    const date = getDate(localDateTime);
    return isNaN(date.getTime()) ? '' : date.toISOString();
}

/**
 * @since 4.8.0
 */
export function formatDateLocal(dateString: string) {
    if (!dateString) return '';
    const dateObj = new Date(dateString);
    if (isNaN(dateObj.getTime())) return '';
    return dateI18n('Y-m-d', dateObj, undefined);
}
