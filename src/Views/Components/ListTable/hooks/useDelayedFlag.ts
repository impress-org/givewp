import {useEffect, useState} from 'react';

/**
 * Turns on `delayMs` after `active` became true, and off at once when it becomes false. A short
 * wait never turns it on, so a fast request shows nothing.
 *
 * @since TBD
 */
export default function useDelayedFlag(active: boolean, delayMs: number): boolean {
    const [on, setOn] = useState(false);

    useEffect(() => {
        if (!active) {
            setOn(false);
            return undefined;
        }

        const timer = window.setTimeout(() => setOn(true), delayMs);

        return () => window.clearTimeout(timer);
    }, [active, delayMs]);

    return on;
}
