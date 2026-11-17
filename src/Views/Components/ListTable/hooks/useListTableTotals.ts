import {useCallback, useMemo, useRef} from 'react';
import useListTableQuery from './useListTableQuery';
import type {ListTableParameters, ListTableResponse} from '../ListTablePage/types';

/**
 * @since TBD
 */
export type ListTableTotals = {totalItems: number; totalPages: number};

type CountResponse = {totalItems?: number | string; totalPages?: number};

/**
 * The request that counts the matching records: the same parameters as the rows, without the page and the
 * sort, so changing page or sort does not count again.
 *
 * @since TBD
 */
export function countParameters(parameters: ListTableParameters): ListTableParameters {
    const {page, sortColumn, sortDirection, ...rest} = parameters;

    return rest;
}

function toTotals(response: {totalItems?: number | string; totalPages?: number} | undefined): ListTableTotals | null {
    if (!response || response.totalItems === undefined || response.totalPages === undefined) {
        return null;
    }

    const totalItems = Number(response.totalItems);

    return Number.isFinite(totalItems) ? {totalItems, totalPages: Number(response.totalPages)} : null;
}

/**
 * The total of a list, which the rows request no longer waits for. The total is `null` until it is known:
 *
 * 1. totals in the rows response of this view (the server sends them when it has them already, and old
 *    endpoints always do),
 * 2. else the answer of the count request, which is keyed without page and sort and never keeps the answer
 *    of another request, so a response for earlier filters never shows,
 * 3. else the totals of an earlier rows response for the same filters.
 *
 * A failed count request leaves the total `null`: the table keeps working with its arrows. So does a
 * counted total that the current rows contradict (see `contradictsRows`).
 *
 * `hasNextPage` comes from the rows response; until the first one it is `true`.
 *
 * @since TBD
 */
export default function useListTableTotals({
    apiRoot,
    parameters,
    page,
    data,
    isLoading,
}: {
    apiRoot: string;
    parameters: ListTableParameters;
    page: number;
    data: ListTableResponse | undefined;
    /** True while `data` belongs to an earlier view. */
    isLoading: boolean;
}) {
    const params = useMemo(() => countParameters(parameters), [parameters]);
    const countKey = JSON.stringify(params);
    const {data: count, refresh} = useListTableQuery<CountResponse>(`${apiRoot}/count`, params, {
        keepPreviousData: false,
    });

    const fromRows = isLoading ? null : toTotals(data);
    const remembered = useRef<{key: string; totals: ListTableTotals} | null>(null);

    if (fromRows) {
        remembered.current = {key: countKey, totals: fromRows};
    }

    const counted = fromRows ?? toTotals(count) ?? (remembered.current?.key === countKey ? remembered.current.totals : null);

    // The rows are the evidence: a counted total that says there is no page after this one (or one more)
    // while the rows say the opposite is out of date. Showing it would hide the rest of the list behind a
    // numbered pager, so the table keeps its arrows until the count agrees with the rows.
    const contradictsRows =
        !fromRows &&
        !!counted &&
        !isLoading &&
        typeof data?.hasNextPage === 'boolean' &&
        data.hasNextPage !== page < counted.totalPages;
    const totals = contradictsRows ? null : counted;

    const hasNextPage = data?.hasNextPage ?? (totals && data ? page < totals.totalPages : true);

    const refreshTotals = useCallback(() => refresh(), [refresh]);

    return {totals, hasNextPage, refreshTotals};
}
