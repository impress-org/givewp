import {useCallback, useState} from 'react';
import type {View} from '@wordpress/dataviews';

const isApplied = (value: unknown) =>
    value !== undefined && value !== null && value !== '' && !(Array.isArray(value) && value.length === 0);

/**
 * The parts of a view that change which rows match. A change to any of them starts again at page one.
 */
const queryOf = (view: View) => JSON.stringify([view.search, view.filters, view.sort, view.perPage]);

/**
 * Owns the list view (page, page size, sort, search, filters, columns) for the life of the page.
 *
 * @since TBD
 */
export default function useListTableView(initialView: View | (() => View)) {
    const [view, setView] = useState<View>(initialView);

    const onChangeView = useCallback((next: View) => {
        setView((previous) => (queryOf(next) === queryOf(previous) ? next : {...next, page: 1}));
    }, []);

    const hasActiveFilters =
        isApplied(view.search) || (view.filters ?? []).some((filter) => !filter.isLocked && isApplied(filter.value));

    /**
     * Clears the search term and every filter the admin can change. Sort and page size stay.
     */
    const clearFilters = useCallback(() => {
        setView((previous) => ({
            ...previous,
            page: 1,
            search: '',
            filters: (previous.filters ?? []).filter((filter) => filter.isLocked),
        }));
    }, []);

    return {view, onChangeView, hasActiveFilters, clearFilters};
}
