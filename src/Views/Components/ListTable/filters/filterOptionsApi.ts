import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import type {ListTableScreenKey} from '../state/types';
import type {FilterEntitySource, FilterOption} from './types';

/**
 * One page of options from the filter options route.
 *
 * @since TBD
 */
export type FilterOptionsPage = {options: FilterOption[]; hasMore: boolean};

/**
 * @since TBD
 */
export type FetchFilterOptionsArgs = {
    source: FilterEntitySource;
    screen?: ListTableScreenKey;
    search?: string;
    page?: number;
    /** Ids to look up by label. Returns those ids only, with no search and no paging. */
    include?: string[];
};

/**
 * Asks the server for filter options: a page of them, or the labels of known ids.
 *
 * @since TBD
 */
export function fetchFilterOptions({
    source,
    screen,
    search,
    page,
    include,
}: FetchFilterOptionsArgs): Promise<FilterOptionsPage> {
    const path = addQueryArgs(`/givewp/v3/list-table-filter-options/${source}`, {
        screen,
        search: search || undefined,
        page: include ? undefined : page ?? 1,
        include: include?.length ? include : undefined,
    });

    return apiFetch<FilterOptionsPage>({path});
}
