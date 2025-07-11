import {formatDistanceToNow} from 'date-fns';

/**
 * @since 4.0.0
 */
export function amountFormatter(currency: Intl.NumberFormatOptions['currency'], options?: Intl.NumberFormatOptions): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options,
    });
}

/**
 * @since unreleased
 */
export function formatTimestamp(timestamp: string, useComma: boolean = false): string {
    const date = new Date(timestamp);

    const day = date.getDate();
    const ordinal = (day: number): string => {
        if (day > 3 && day < 21) return 'th';
        switch (day % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
            default: return 'th';
        }
    };

    const dayWithOrdinal = `${day}${ordinal(day)}`;
    const month = date.toLocaleString('en-US', { month: 'long' });
    const year = date.getFullYear();
    const time = date.toLocaleString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }).toLowerCase();
    const separator = useComma ? ', ' : ' • ';

    return `${dayWithOrdinal} ${month} ${year}${separator}${time}`;
}

/**
 * Returns a relative time string for a given date (e.g. "Today" or "2 days ago")
 *
 * @since unreleased
 */
export function getRelativeTimeString(date: Date): string {
    const now = new Date();
    if (date.toDateString() === now.toDateString()) {
        return 'Today';
    }
    return formatDistanceToNow(date, {addSuffix: true});
}
