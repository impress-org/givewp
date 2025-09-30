import {formatDistanceToNow} from 'date-fns';

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
