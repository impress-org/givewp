import {useCallback, useEffect, useRef, useState} from 'react';
import {useDebounce} from '@wordpress/compose';
import {fetchFilterOptions} from '../filters/filterOptionsApi';
import {rememberLabels} from '../filters/labelCache';
import type {FilterEntitySource, FilterOption} from '../filters/types';
import type {ListTableScreenKey} from '../state/types';

const DEBOUNCE_MS = 300;

/**
 * @since TBD
 */
export type UseFilterOptionsArgs = {
    source: FilterEntitySource;
    screen?: ListTableScreenKey;
    /** Nothing is requested until this is true (for example, when the popover opens). */
    enabled: boolean;
};

/**
 * @since TBD
 */
export type UseFilterOptionsResult = {
    options: FilterOption[];
    /** What the admin typed. The request follows after a short pause. */
    search: string;
    setSearch: (search: string) => void;
    isLoading: boolean;
    error?: Error;
    hasMore: boolean;
    loadMore: () => void;
    retry: () => void;
};

type Loaded = {
    /** The request this belongs to: source, screen, term and retry count. */
    key: string;
    options: FilterOption[];
    page: number;
    hasMore: boolean;
    error?: Error;
};

const toError = (reason: unknown): Error => (reason instanceof Error ? reason : new Error(String(reason)));

/**
 * Loads the options of an entity filter (campaign, form, donor) from the server: the first page, a
 * new first page for each search term, and more pages on request. Never loads before `enabled`.
 *
 * @since TBD
 */
export default function useFilterOptions({source, screen, enabled}: UseFilterOptionsArgs): UseFilterOptionsResult {
    const [search, setSearchText] = useState('');
    const [term, setTerm] = useState('');
    const [attempt, setAttempt] = useState(0);
    const [loaded, setLoaded] = useState<Loaded>();
    const [isLoadingMore, setIsLoadingMore] = useState(false);
    // Only the newest request may write its answer.
    const latestRequest = useRef(0);

    const key = `${source}|${screen ?? ''}|${term}|${attempt}`;

    // A stable wrapper: the debounce survives parent re-renders.
    const applyTerm = useCallback((next: string) => setTerm(next), []);
    const debouncedApplyTerm = useDebounce(applyTerm, DEBOUNCE_MS);

    const setSearch = useCallback(
        (next: string) => {
            setSearchText(next);
            debouncedApplyTerm(next);
        },
        [debouncedApplyTerm]
    );

    useEffect(() => {
        if (!enabled) {
            return;
        }

        const request = ++latestRequest.current;
        setIsLoadingMore(false);

        fetchFilterOptions({source, screen, search: term, page: 1}).then(
            ({options, hasMore}) => {
                rememberLabels(source, options);
                request === latestRequest.current && setLoaded({key, options, page: 1, hasMore});
            },
            (reason: unknown) => {
                request === latestRequest.current &&
                    setLoaded({key, options: [], page: 1, hasMore: false, error: toError(reason)});
            }
        );

        // A newer search, or an unmount, makes this answer stale.
        return () => {
            latestRequest.current++;
        };
    }, [enabled, key]);

    const current = loaded?.key === key ? loaded : undefined;

    // Loads the page after `from` and appends it. Only the newest request may write its answer.
    const requestPage = useCallback(
        (from: Loaded) => {
            const request = ++latestRequest.current;
            const nextPage = from.page + 1;
            setIsLoadingMore(true);
            setLoaded({...from, error: undefined});

            fetchFilterOptions({source, screen, search: term, page: nextPage}).then(
                ({options, hasMore}) => {
                    if (request !== latestRequest.current) {
                        return;
                    }
                    rememberLabels(source, options);
                    setIsLoadingMore(false);
                    setLoaded({key, options: [...from.options, ...options], page: nextPage, hasMore});
                },
                (reason: unknown) => {
                    if (request !== latestRequest.current) {
                        return;
                    }
                    setIsLoadingMore(false);
                    setLoaded({...from, error: toError(reason)});
                }
            );
        },
        [source, screen, term, key]
    );

    const loadMore = useCallback(() => {
        if (!current || current.error || !current.hasMore || isLoadingMore) {
            return;
        }

        requestPage(current);
    }, [current, isLoadingMore, requestPage]);

    const retry = useCallback(() => {
        // A failed "load more" keeps its options, so retry asks for that page again.
        if (current?.error && current.options.length > 0) {
            requestPage(current);
            return;
        }

        setAttempt((count) => count + 1);
    }, [current, requestPage]);

    return {
        options: current?.options ?? [],
        search,
        setSearch,
        isLoading: enabled && (isLoadingMore || current === undefined),
        error: current?.error,
        hasMore: current?.hasMore ?? false,
        loadMore,
        retry,
    };
}
