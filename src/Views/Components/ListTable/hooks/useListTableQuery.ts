import {useCallback} from 'react';
import useSWR from 'swr';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import type {ListTableParameters} from '../ListTablePage/types';

/**
 * Turns request parameters into query arguments the way the old client did: empty values are left
 * out and arrays are sent as comma-separated text.
 */
function toQueryArgs(parameters: ListTableParameters): Record<string, string | number | boolean> {
    return Object.fromEntries(
        Object.entries(parameters)
            .filter(([, value]) => value !== undefined && value !== null && value !== '')
            .map(([key, value]) => [key, Array.isArray(value) ? value.join(',') : value])
    );
}

const fetchUrl = <Response>(url: string) => apiFetch<Response>({url});

/**
 * Fetches one page of a list table, or its stats, from its endpoint. The request URL is the cache
 * key, so each view (page, sort, search, filters) has its own cache entry. Pass `null` as the
 * endpoint to fetch nothing.
 *
 * - `keepPreviousData` keeps the previous rows on screen while a new view loads.
 * - `isLoading` is true only while the rows on screen do not belong to the current view.
 * - No refetch on window focus and no automatic retry: the list changes when the view changes or
 *   when an action calls `refresh()`; a failed request shows its error and offers a retry.
 *
 * @since TBD
 */
export default function useListTableQuery<Response>(endpoint: string | null, parameters: ListTableParameters) {
    const url = endpoint ? addQueryArgs(endpoint, toQueryArgs(parameters)) : null;

    const {data, error, isLoading, mutate} = useSWR<Response, Error>(url, fetchUrl, {
        keepPreviousData: true,
        revalidateOnFocus: false,
        shouldRetryOnError: false,
    });

    const refresh = useCallback(() => mutate(), [mutate]);

    return {data, error, isLoading, refresh};
}
