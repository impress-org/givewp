import {type RefObject, useCallback, useEffect, useState} from 'react';

/**
 * @since TBD
 */
export interface HorizontalOverflow {
    /** Content is hidden before the start edge (left in LTR, right in RTL). */
    hasMoreStart: boolean;
    /** Content is hidden past the end edge (right in LTR, left in RTL). */
    hasMoreEnd: boolean;
}

const NONE: HorizontalOverflow = {hasMoreStart: false, hasMoreEnd: false};

// The stock DataViews element that scrolls sideways when the table is wider than the page.
const SCROLL_CONTAINER = '.dataviews-wrapper';

function measure(element: HTMLElement | null): HorizontalOverflow {
    if (!element) {
        return NONE;
    }

    // In RTL, scrollLeft goes from 0 down to a negative number, so the distance from the start
    // edge is the absolute value in both directions.
    const start = Math.abs(element.scrollLeft);

    return {
        hasMoreStart: start > 1,
        // One pixel of slack, because browsers round the sizes.
        hasMoreEnd: start + element.clientWidth < element.scrollWidth - 1,
    };
}

// The stock table inside the scroll container. The search row, filters and footer sit around it.
const TABLE = '.dataviews-view-table';

/**
 * Tells whether the list's scroll container hides content at its start or end edge, so the list
 * can mark that side with a class. It checks again on scroll, on resize and after every render (new
 * rows or columns change the content width).
 *
 * @since TBD
 */
export default function useHorizontalOverflow(rootRef: RefObject<HTMLElement>): HorizontalOverflow {
    const [overflow, setOverflow] = useState<HorizontalOverflow>(NONE);

    const update = useCallback(() => {
        const next = measure(rootRef.current?.querySelector<HTMLElement>(SCROLL_CONTAINER) ?? null);

        setOverflow((current) =>
            current.hasMoreStart === next.hasMoreStart && current.hasMoreEnd === next.hasMoreEnd ? current : next
        );
    }, [rootRef]);

    useEffect(() => {
        const element = rootRef.current?.querySelector<HTMLElement>(SCROLL_CONTAINER);

        if (!element) {
            return;
        }

        element.addEventListener('scroll', update, {passive: true});
        const observer = typeof ResizeObserver === 'undefined' ? null : new ResizeObserver(update);
        observer?.observe(element);
        // The table can change width without the scroll container changing size (a column added or removed).
        const table = element.querySelector<HTMLElement>(TABLE);

        if (table) {
            observer?.observe(table);
        }

        return () => {
            element.removeEventListener('scroll', update);
            observer?.disconnect();
        };
    }, [rootRef, update]);

    // No dependency list on purpose: the content width can change on any render. A render that changes
    // nothing only costs the reads, because the state is kept when the values did not change.
    useEffect(update);

    return overflow;
}
