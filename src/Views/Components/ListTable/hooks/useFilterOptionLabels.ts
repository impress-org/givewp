import {useCallback, useEffect, useRef, useState, useSyncExternalStore} from 'react';
import {
    claimUnknownValues,
    knownLabel,
    labelsVersion,
    releaseValues,
    rememberLabels,
    subscribeToLabels,
} from '../filters/labelCache';
import {fetchFilterOptions} from '../filters/filterOptionsApi';
import {NONE_VALUE} from '../filters/filterKinds';
import type {FilterEntitySource} from '../filters/types';
import type {ListTableScreenKey} from '../state/types';

/** The exclusive "none" choice is not a record, so it has nothing to look up. */

/** The most ids the route accepts in one `include` request. */
const MAX_INCLUDE = 50;

/** How long to wait before each new try after a failed request. One entry per try. */
const RETRY_DELAYS_MS = [1000, 3000];

const chunk = <T>(items: T[], size: number): T[][] =>
    Array.from({length: Math.ceil(items.length / size)}, (_, index) => items.slice(index * size, (index + 1) * size));

/**
 * Finds the labels of selected ids, so a chip can show a name for an id that is not loaded. An id
 * with no known label is requested once. Returns a lookup that gives the label, or undefined. The
 * component renders again whenever the source gets a new label, whoever asked for it. A failed
 * request is tried again twice, after a short wait, while the same ids stay selected.
 *
 * @since TBD
 */
export default function useFilterOptionLabels(
    source: FilterEntitySource,
    screen: ListTableScreenKey | undefined,
    values: string[]
): (value: string) => string | undefined {
    const getVersion = useCallback(() => labelsVersion(source), [source]);
    const version = useSyncExternalStore(subscribeToLabels, getVersion);
    // Depend on the text of the ids, not on the array, which is new on every render.
    const key = values.join(',');
    // The tries made for one lookup. A new lookup starts again from zero.
    const lookup = `${source}|${screen ?? ''}|${key}`;
    const [retries, setRetries] = useState({lookup, round: 0});
    const round = retries.lookup === lookup ? retries.round : 0;
    // Runs the lookup again after a request failed in an effect that new ids had already replaced.
    const [lateRetry, setLateRetry] = useState(0);
    const mounted = useRef(true);

    useEffect(() => {
        mounted.current = true;

        return () => {
            mounted.current = false;
        };
    }, []);

    useEffect(() => {
        const wanted = key === '' ? [] : key.split(',').filter((value) => value !== NONE_VALUE);
        let active = true;
        let retryTimer: ReturnType<typeof setTimeout> | undefined;

        const scheduleRetry = () => {
            if (!active || retryTimer !== undefined || round >= RETRY_DELAYS_MS.length) {
                return;
            }
            retryTimer = setTimeout(() => setRetries({lookup, round: round + 1}), RETRY_DELAYS_MS[round]);
        };

        // Each chunk is claimed and released on its own, so a failed one can be asked for again.
        chunk(claimUnknownValues(source, wanted), MAX_INCLUDE).forEach((missing) => {
            fetchFilterOptions({source, screen, include: missing})
                // Every consumer of this source renders again, even if its ids changed meanwhile.
                .then(({options}) => rememberLabels(source, options))
                .catch(() => {
                    releaseValues(source, missing);

                    if (active) {
                        scheduleRetry();
                        return;
                    }

                    // New ids replaced this effect while the request ran. Its ids may still be selected, and the
                    // current effect will not ask for them again unless it runs again.
                    setTimeout(() => {
                        if (mounted.current) {
                            setLateRetry((count) => count + 1);
                        }
                    }, RETRY_DELAYS_MS[0]);
                });
        });

        // An unmount or new ids cancel the next try.
        return () => {
            active = false;
            clearTimeout(retryTimer);
        };
    }, [source, screen, key, lookup, round, lateRetry]);

    // A new function each time the cache changes, so memoized chip text recomputes.
    return useCallback((value: string) => knownLabel(source, value), [source, version]);
}
