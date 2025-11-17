import {dateI18n, getDate} from '@wordpress/date';

/**
 * Format the date to a date-local input compatible string
 * @since 4.13.0
 */
export default function formatToDateLocalInput(dateString: string) {
    if (!dateString) {
        return '';
    }

    // Interpret server-provided naive strings as site timezone (WordPress timezone),
    // and preserve the wall time for the date input.
    const dateObj = getDate(dateString);
    if (isNaN(dateObj.getTime())) {
        return '';
    }

    return dateI18n('Y-m-d', dateObj, undefined);
}
