import {writeViewQuery} from './viewQuery';
import type {WriteViewQueryOptions} from './viewQuery';

/**
 * A search-only change this soon after the previous one replaces the entry instead of adding one, so
 * one typed term is one history entry even with pauses.
 *
 * @since TBD
 */
export const SEARCH_COLLAPSE_MS = 1500;

/**
 * Which of the screen's parameters a write replaces: its own `give-` keys and the legacy names it read.
 *
 * @since TBD
 */
export type HistoryWriteOptions = Omit<WriteViewQueryOptions, 'addressLength'>;

let lastSearchChangeAt = -Infinity;

/**
 * The address's query string with the view's parameters written into it.
 */
function nextQuery(parameters: Record<string, string>, options: HistoryWriteOptions): string {
    const {origin, pathname, hash, search} = window.location;

    return writeViewQuery(search, parameters, {
        ...options,
        addressLength: origin.length + pathname.length + hash.length,
    });
}

function write(method: 'pushState' | 'replaceState', parameters: Record<string, string>, options: HistoryWriteOptions) {
    const {pathname, hash} = window.location;

    // The state of the entry is kept as it is: only the address changes.
    window.history[method](window.history.state, '', `${pathname}${nextQuery(parameters, options)}${hash}`);
}

/**
 * The address's query parameters.
 *
 * @since TBD
 */
export function readQuery(): URLSearchParams {
    return new URLSearchParams(window.location.search);
}

/**
 * Adds a history entry for a user change. A search-only change within `SEARCH_COLLAPSE_MS` of the
 * previous search-only change replaces the entry instead.
 *
 * @since TBD
 */
export function pushEntry(
    parameters: Record<string, string>,
    options: HistoryWriteOptions,
    {searchOnly = false}: {searchOnly?: boolean} = {}
) {
    const now = Date.now();
    const collapse = searchOnly && now - lastSearchChangeAt < SEARCH_COLLAPSE_MS;

    lastSearchChangeAt = searchOnly ? now : -Infinity;
    write(collapse ? 'replaceState' : 'pushState', parameters, options);
}

/**
 * Replaces the current history entry, for corrections and legacy rewrites. It adds no entry.
 *
 * @since TBD
 */
export function replaceEntry(parameters: Record<string, string>, options: HistoryWriteOptions) {
    write('replaceState', parameters, options);
}

/**
 * Calls back when the browser moves through history (back and forward). Returns the unsubscribe.
 *
 * @since TBD
 */
export function onPopState(callback: () => void): () => void {
    window.addEventListener('popstate', callback);

    return () => window.removeEventListener('popstate', callback);
}
