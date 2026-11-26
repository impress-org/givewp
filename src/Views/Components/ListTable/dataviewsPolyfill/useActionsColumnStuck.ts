import {type RefObject, useCallback, useEffect, useLayoutEffect} from 'react';
import {isRTL} from '@wordpress/i18n';

// Stock DataViews class names: the element that scrolls sideways, the table, the pinned Actions cells,
// and the class that draws the line on their edge.
const SCROLL_CONTAINER = '.dataviews-wrapper';
const TABLE = '.dataviews-view-table';
const PINNED_CELL = '.dataviews-view-table__actions-column--sticky';
const STUCK = 'dataviews-view-table__actions-column--stuck';

// The same test as DataViews, with one pixel of slack because browsers round the sizes.
function isScrolledToEnd(element: HTMLElement): boolean {
    return isRTL()
        ? Math.abs(element.scrollLeft) <= 1
        : element.scrollLeft + element.clientWidth >= element.scrollWidth - 1;
}

/**
 * Keeps the stock line on the pinned Actions column up to date, as WordPress 7.1 does: at once on scroll,
 * on resize and after every render. The pinned DataViews measures only on mount, scroll and window resize,
 * and 200 ms late, so a list that loads slowly has no line until the user scrolls.
 *
 * It only toggles the stock class, which DataViews later sets to the same value.
 *
 * @since TBD
 */
export default function useActionsColumnStuck(rootRef: RefObject<HTMLElement>): void {
    const update = useCallback(() => {
        const element = rootRef.current?.querySelector<HTMLElement>(SCROLL_CONTAINER);

        if (!element) {
            return;
        }

        const stuck = !isScrolledToEnd(element);
        element.querySelectorAll(PINNED_CELL).forEach((cell) => cell.classList.toggle(STUCK, stuck));
    }, [rootRef]);

    useEffect(() => {
        const element = rootRef.current?.querySelector<HTMLElement>(SCROLL_CONTAINER);

        if (!element) {
            return;
        }

        element.addEventListener('scroll', update, {passive: true});
        const observer = typeof ResizeObserver === 'undefined' ? null : new ResizeObserver(update);
        observer?.observe(element);
        // The table can change width without the scroll container changing size.
        const table = element.querySelector<HTMLElement>(TABLE);

        if (table) {
            observer?.observe(table);
        }

        return () => {
            element.removeEventListener('scroll', update);
            observer?.disconnect();
        };
    });

    // No dependency list on purpose: new rows or columns can arrive on any render.
    useLayoutEffect(update);
}
