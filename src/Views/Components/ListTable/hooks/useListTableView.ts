import {useCallback, useState} from 'react';
import type {View} from '@wordpress/dataviews';
import {isApplied} from '../adapters/viewToParameters';
import type {ListTableView} from '../state/types';

/**
 * Where a view change came from, so the URL knows whether to write: `user` adds a history entry,
 * `correction` replaces it, `history` (back and forward) writes nothing, and `reset` behaves like a
 * user change. `load` is the first view.
 *
 * @since TBD
 */
export type ViewChangeOrigin = 'load' | 'user' | 'history' | 'correction' | 'reset';

/**
 * The origins of a view set as it is, not through DataViews.
 *
 * @since TBD
 */
export type ReplaceOrigin = Extract<ViewChangeOrigin, 'correction' | 'history' | 'reset'>;

/**
 * The parts of a view that change which rows match. A change to any of them starts again at page one.
 */
const queryOf = (view: View) => JSON.stringify([view.search, view.filters, view.sort, view.perPage]);

/**
 * Owns the list view (page, page size, sort, search, filters, columns) for the life of the page, and
 * where its last change came from.
 *
 * @since TBD
 */
export default function useListTableView(initialView: ListTableView | (() => ListTableView)) {
    const [state, setState] = useState<{view: ListTableView; origin: ViewChangeOrigin}>(() => ({
        view: typeof initialView === 'function' ? initialView() : initialView,
        origin: 'load',
    }));
    const {view, origin} = state;

    // DataViews hands back the view it was given, so a table view stays a table view.
    const onChangeView = useCallback((next: View) => {
        const nextView = next as ListTableView;

        setState((previous) => ({
            view: queryOf(nextView) === queryOf(previous.view) ? nextView : {...nextView, page: 1},
            origin: 'user',
        }));
    }, []);

    /**
     * Sets the view as it is, without the page reset of a user change.
     */
    const replaceView = useCallback((next: ListTableView, nextOrigin: ReplaceOrigin = 'correction') => {
        setState({view: next, origin: nextOrigin});
    }, []);

    const hasActiveFilters =
        isApplied(view.search) || (view.filters ?? []).some((filter) => !filter.isLocked && isApplied(filter.value));

    /**
     * Clears the search term and every filter the admin can change. Sort and page size stay.
     */
    const clearFilters = useCallback(() => {
        setState((previous) => ({
            view: {
                ...previous.view,
                page: 1,
                search: '',
                filters: (previous.view.filters ?? []).filter((filter) => filter.isLocked),
            },
            origin: 'user',
        }));
    }, []);

    return {view, origin, onChangeView, replaceView, hasActiveFilters, clearFilters};
}
