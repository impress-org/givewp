import {useCallback, useEffect, useState} from 'react';
import {claimUnknownValues, knownLabel, releaseValues, rememberLabels} from '../filters/labelCache';
import {fetchFilterOptions} from '../filters/filterOptionsApi';
import type {FilterEntitySource} from '../filters/types';
import type {ListTableScreenKey} from '../state/types';

/** The exclusive "none" choice is not a record, so it has nothing to look up. */
const NONE_VALUE = 'none';

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
    const [, setVersion] = useState(0);
    // Depend on the text of the ids, not on the array, which is new on every render.
    const key = values.join(',');

    useEffect(() => {
        const wanted = key === '' ? [] : key.split(',').filter((value) => value !== NONE_VALUE);
        const missing = claimUnknownValues(source, wanted);

        if (missing.length === 0) {
            return;
        }

        let active = true;

        fetchFilterOptions({source, screen, include: missing})
            .then(({options}) => {
                rememberLabels(source, options);
                active && setVersion((version) => version + 1);
            })
            .catch(() => releaseValues(source, missing));

        return () => {
            active = false;
        };
    }, [source, screen, key]);

    return useCallback((value: string) => knownLabel(source, value), [source, key]);
}
