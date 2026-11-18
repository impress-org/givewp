import {useEffect, useState} from 'react';
import type {View} from '@wordpress/dataviews';

/**
 * Owns the selected row ids. Selection belongs to one page of results, so it clears when the page,
 * page size, sort, filters or search change.
 *
 * @since TBD
 */
export default function useListTableSelection(view: View) {
    const [selection, setSelection] = useState<string[]>([]);
    const resultsKey = JSON.stringify([view.page, view.perPage, view.sort, view.filters, view.search]);

    useEffect(() => {
        setSelection([]);
    }, [resultsKey]);

    return {selection, onChangeSelection: setSelection};
}
