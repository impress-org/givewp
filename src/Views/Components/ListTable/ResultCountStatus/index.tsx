import {useEffect, useRef, useState} from 'react';
import {__, _n, sprintf} from '@wordpress/i18n';

/**
 * How long the result must stay the same before it is announced, so quick changes give one message.
 */
const SETTLE_DELAY = 300;

/**
 * Added to the end of a message that repeats the one before it, so screen readers read it again.
 * Written as an escape so it is visible in review.
 */
const NO_BREAK_SPACE = '\u00A0';

/**
 * @since TBD
 */
export interface ResultCountStatusProps {
    /** Changes when the admin changes what the list asks for: filters, search, sort or page. */
    requestKey: string;
    isLoading: boolean;
    /** The number of matching records, or undefined while there is no answer (or it failed). */
    totalItems: number | undefined;
    /** Record type, singular, for example "donation". */
    singleName: string;
    /** Record type, plural, for example "donations". */
    pluralName: string;
}

function resultMessage(totalItems: number, singleName: string, pluralName: string): string {
    if (totalItems === 0) {
        /* translators: %s: record type, plural (for example "donations"). */
        return sprintf(__('No %s found', 'give'), pluralName);
    }

    return sprintf(
        /* translators: 1: number of records, 2: record type, singular or plural (for example "donations"). */
        _n('%1$d %2$s found', '%1$d %2$s found', totalItems, 'give'),
        totalItems,
        totalItems === 1 ? singleName : pluralName
    );
}

/**
 * A visually hidden status region that tells screen reader users how many records the list shows
 * after they change the filters, the search or the page. It says nothing on the first load and
 * nothing while the list loads, and it adds no request.
 *
 * @since TBD
 */
export default function ResultCountStatus({
    requestKey,
    isLoading,
    totalItems,
    singleName,
    pluralName,
}: ResultCountStatusProps) {
    const [message, setMessage] = useState('');
    // The request whose result was last accounted for. The first one is the page load, so it is not
    // announced, even when it failed.
    const settledKey = useRef<string | null>(null);

    useEffect(() => {
        if (isLoading) {
            return;
        }

        if (settledKey.current === null) {
            settledKey.current = requestKey;
            return;
        }

        if (totalItems === undefined || settledKey.current === requestKey) {
            return;
        }

        const timer = setTimeout(() => {
            settledKey.current = requestKey;
            const text = resultMessage(totalItems, singleName, pluralName);
            // The same words twice would not be read again, so the second one gets a trailing no-break space.
            setMessage((previous) => (previous === text ? `${text}${NO_BREAK_SPACE}` : text));
        }, SETTLE_DELAY);

        return () => clearTimeout(timer);
    }, [requestKey, isLoading, totalItems, singleName, pluralName]);

    return (
        <div role="status" aria-live="polite" aria-atomic="true" className="screen-reader-text">
            {message}
        </div>
    );
}
