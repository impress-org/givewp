import {useMediaQuery} from '@wordpress/compose';
import {NARROW_LAYOUT_QUERY} from '../layout/breakpoints';

/**
 * True while the window is at or below 782 px, where the list shows cards. Follows the window as it
 * is resized. False where the browser cannot match media queries.
 *
 * @since TBD
 */
export default function useNarrowLayout(): boolean {
    // The installed `useMediaQuery` calls `matchMedia` without checking that it exists.
    const canMatch = typeof window !== 'undefined' && typeof window.matchMedia === 'function';

    return useMediaQuery(canMatch ? NARROW_LAYOUT_QUERY : undefined);
}
