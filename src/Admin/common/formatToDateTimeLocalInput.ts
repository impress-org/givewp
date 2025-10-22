import {dateI18n, getDate} from '@wordpress/date';

/**
 * @since 4.6.0
 */
export default function formatToDateTimeLocalInput(dateString: string) {
    if (!dateString) {
        return '';
    }

    // Interpret server-provided naive strings as site timezone (WordPress timezone),
    // and preserve the wall time for the datetime-local input.
    const dateObj = getDate(dateString);
    if (isNaN(dateObj.getTime())) {
        return '';
    }

    return dateI18n('Y-m-d\\TH:i', dateObj, undefined);
}
