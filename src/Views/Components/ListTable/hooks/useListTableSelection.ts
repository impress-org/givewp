import {useEffect, useState} from 'react';
import type {View} from '@wordpress/dataviews';
import useEscapeKey from './useEscapeKey';

/**
 * Owns the selected row ids. Selection belongs to one page of results, so it clears when the page,
 * page size, sort, filters or search change, and when Esc is pressed with no popover, menu or dialog open.
 *
 * @since TBD
 */
export default function useListTableSelection(view: View) {
    const [selection, setSelection] = useState<string[]>([]);
    const resultsKey = JSON.stringify([view.page, view.perPage, view.sort, view.filters, view.search]);

    useEffect(() => {
        setSelection([]);
    }, [resultsKey]);

    useEscapeKey(() => setSelection([]), selection.length > 0);

    return {selection, onChangeSelection: setSelection};
}
