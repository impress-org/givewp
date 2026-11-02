import {useCallback, useEffect, useRef, useState} from 'react';
import {claimUnknownValues, knownLabel, releaseValues, rememberLabels} from '../filters/labelCache';
import {fetchFilterOptions} from '../filters/filterOptionsApi';
import type {FilterEntitySource} from '../filters/types';
import type {ListTableScreenKey} from '../state/types';

/** The exclusive "none" choice is not a record, so it has nothing to look up. */
const NONE_VALUE = 'none';

/** The most ids the route accepts in one `include` request. */
const MAX_INCLUDE = 50;

const chunk = <T>(items: T[], size: number): T[][] =>
    Array.from({length: Math.ceil(items.length / size)}, (_, index) => items.slice(index * size, (index + 1) * size));

/**
 * Finds the labels of selected ids, so a chip can show a name for an id that is not loaded. An id
 * with no known label is requested once. Returns a lookup that gives the label, or undefined.
 *
 * @since TBD
 */
export default function useFilterOptionLabels(
    source: FilterEntitySource,
    screen: ListTableScreenKey | undefined,
    values: string[]
): (value: string) => string | undefined {
    const [version, setVersion] = useState(0);
    const mounted = useRef(true);
    // Depend on the text of the ids, not on the array, which is new on every render.
    const key = values.join(',');

    useEffect(() => {
        mounted.current = true;

        return () => {
            mounted.current = false;
        };
    }, []);

    useEffect(() => {
        const wanted = key === '' ? [] : key.split(',').filter((value) => value !== NONE_VALUE);

        // Each chunk is claimed and released on its own, so a failed one can be asked for again.
        chunk(claimUnknownValues(source, wanted), MAX_INCLUDE).forEach((missing) => {
            fetchFilterOptions({source, screen, include: missing})
                .then(({options}) => {
                    rememberLabels(source, options);
                    // Refresh even if the ids changed meanwhile: the answer may be shown by the new ones.
                    mounted.current && setVersion((count) => count + 1);
                })
                .catch(() => releaseValues(source, missing));
        });
    }, [source, screen, key]);

    // A new function each time the cache changes, so memoized chip text recomputes.
    return useCallback((value: string) => knownLabel(source, value), [source, version]);
}
