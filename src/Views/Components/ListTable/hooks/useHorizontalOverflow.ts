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
 * The whole list scrolls sideways, not only the table. So the edge shadows are limited to the
 * table's rows with two CSS variables (the space above and below the table).
 */
function placeShadows(root: HTMLElement | null): void {
    const table = root?.querySelector<HTMLElement>(TABLE);

    if (!root || !table) {
        return;
    }

    const rootBox = root.getBoundingClientRect();
    const tableBox = table.getBoundingClientRect();

    setIfChanged(root, '--givewp-list-table-shadow-top', `${Math.max(0, tableBox.top - rootBox.top)}px`);
    setIfChanged(root, '--givewp-list-table-shadow-bottom', `${Math.max(0, rootBox.bottom - tableBox.bottom)}px`);
}

// Writing a style property that did not change still makes the browser check styles again.
function setIfChanged(element: HTMLElement, name: string, value: string): void {
    if (element.style.getPropertyValue(name) !== value) {
        element.style.setProperty(name, value);
    }
}

/**
 * Tells whether the list's scroll container hides content at its start or end edge, so the list
 * can show a shadow on that side. It checks again on scroll, on resize and after every render (new
 * rows or columns change the content width).
 *
 * @since TBD
 */
export default function useHorizontalOverflow(rootRef: RefObject<HTMLElement>): HorizontalOverflow {
    const [overflow, setOverflow] = useState<HorizontalOverflow>(NONE);

    const update = useCallback(() => {
        placeShadows(rootRef.current);

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
        // The table can change height without the scroll container changing size (a row added or removed).
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
    // nothing only costs the reads, because the writes above skip unchanged values.
    useEffect(update);

    return overflow;
}
