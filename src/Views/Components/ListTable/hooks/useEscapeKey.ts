import {useEffect, useRef} from 'react';

/**
 * What counts as an open popover, menu or dialog: Esc belongs to it first. Menus and lists render inside a
 * popover, so only those are named: the admin bar keeps `role="menu"` lists in the page all the time.
 */
const OVERLAY = '[role="dialog"], .components-popover, .components-modal__screen-overlay';

/**
 * Runs `onEscape` when Esc is pressed anywhere on the page while `enabled`. It stays out of the way of
 * anything that already used the key: an event another handler prevented, or an open popover, menu or dialog.
 *
 * @since TBD
 */
export default function useEscapeKey(onEscape: () => void, enabled = true) {
    const handler = useRef(onEscape);
    handler.current = onEscape;

    useEffect(() => {
        if (!enabled) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key !== 'Escape' || event.defaultPrevented || document.querySelector(OVERLAY)) {
                return;
            }

            handler.current();
        };

        document.addEventListener('keydown', onKeyDown);

        return () => document.removeEventListener('keydown', onKeyDown);
    }, [enabled]);
}
