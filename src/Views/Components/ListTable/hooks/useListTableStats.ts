import {useMemo} from 'react';
import useListTableQuery from './useListTableQuery';

/**
 * @since TBD
 */
export type StatsStatus = 'loading' | 'ready' | 'refreshing' | 'error';

/**
 * The numbers of a stats endpoint, and the display text the server made for each one.
 *
 * @since TBD
 */
export type ListTableStats = {
    values: Record<string, number>;
    formatted: Record<string, string>;
};

type StatsResponse = Record<string, unknown> & {formatted?: Record<string, string>};

/**
 * - an error and no request running, and no data to show: `error`
 * - an error and no request running, but earlier data: `ready` (the last good numbers stay)
 * - no data yet (or a retry after an error): `loading`
 * - data and a request running: `refreshing`
 * - otherwise: `ready`
 *
 * @since TBD
 */
export function statsStatus({
    hasData,
    hasError,
    isValidating,
}: {
    hasData: boolean;
    hasError: boolean;
    isValidating: boolean;
}): StatsStatus {
    if (hasError && !isValidating) {
        return hasData ? 'ready' : 'error';
    }

    if (!hasData || hasError) {
        return 'loading';
    }

    return isValidating ? 'refreshing' : 'ready';
}

function toStats(response: StatsResponse): ListTableStats {
    const values: Record<string, number> = {};
    const formatted: Record<string, string> = {};

    for (const [key, value] of Object.entries(response)) {
        if (key !== 'formatted' && typeof value === 'number') {
            values[key] = value;
            // No number formatting in the browser: without server text, show the plain number.
            formatted[key] = response.formatted?.[key] ?? String(value);
        }
    }

    return {values, formatted};
}

/**
 * Loads the stats of a list screen. Pass `null` as the endpoint to load nothing.
 *
 * @since TBD
 */
export default function useListTableStats(endpoint: string | null, testMode: boolean) {
    const {data, error, isValidating, refresh} = useListTableQuery<StatsResponse>(endpoint, {testMode});
    const stats = useMemo(() => (data ? toStats(data) : undefined), [data]);

    return {
        stats,
        status: statsStatus({hasData: Boolean(data), hasError: Boolean(error), isValidating}),
        refresh,
    };
}
