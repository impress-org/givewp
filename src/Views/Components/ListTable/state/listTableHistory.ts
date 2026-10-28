import type {ListTableScreenKey} from './types';
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
 * What a list table keeps in the tab's history entry. The search term lives here, never in the address.
 *
 * @since TBD
 */
export type ListTableTabState = {screen: ListTableScreenKey; search: string};

/**
 * The tab's `history.state`. Keys that are not the list table's are kept untouched.
 *
 * @since TBD
 */
export type ListTableHistoryState = {giveListTable?: ListTableTabState; [otherKey: string]: unknown};

/**
 * Which of the screen's parameters a write replaces: its own `give-` keys and the legacy names it read.
 *
 * @since TBD
 */
export type HistoryWriteOptions = Omit<WriteViewQueryOptions, 'addressLength'>;

let lastSearchChangeAt = -Infinity;

const currentState = (): ListTableHistoryState => {
    const state = window.history.state;

    return state !== null && typeof state === 'object' ? state : {};
};

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

/**
 * The session storage key of the search copy for a screen at the current address (without the hash).
 * The query is normalized, because WordPress core rewrites the address on load and may encode a character
 * such as a comma that was written plain.
 */
const searchCopyKey = (screen: ListTableScreenKey) =>
    `give-list-search:${screen}:${window.location.pathname}?${new URLSearchParams(window.location.search)}`;

/**
 * Keeps a copy of the tab's search in session storage, which also lives only as long as the tab.
 * WordPress core's admin canonical-URL script runs `history.replaceState(null, …)` on every admin
 * page load, so after a reload `history.state` is empty and this copy is what restores the search.
 * Blocked storage only loses the copy.
 */
function writeSearchCopy({screen, search}: ListTableTabState) {
    try {
        if (search) {
            window.sessionStorage.setItem(searchCopyKey(screen), search);
        } else {
            window.sessionStorage.removeItem(searchCopyKey(screen));
        }
    } catch {
        // Storage is blocked or full: history.state still holds the search.
    }
}

/**
 * Makes the session copy hold the search of the entry the admin moved to with back or forward. Without
 * this, a reload on that entry would bring back the search of the entry they came from.
 *
 * @since TBD
 */
export function rememberSearch(tabState: ListTableTabState) {
    writeSearchCopy(tabState);
}

function readSearchCopy(screen: ListTableScreenKey): string | null {
    try {
        return window.sessionStorage.getItem(searchCopyKey(screen));
    } catch {
        return null;
    }
}

function write(
    method: 'pushState' | 'replaceState',
    parameters: Record<string, string>,
    options: HistoryWriteOptions,
    tabState: ListTableTabState
) {
    const {pathname, hash} = window.location;
    const state: ListTableHistoryState = {...currentState(), giveListTable: tabState};

    window.history[method](state, '', `${pathname}${nextQuery(parameters, options)}${hash}`);
    writeSearchCopy(tabState);
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
 * The list table state of the current history entry, when it belongs to this screen. When the entry's
 * state was cleared (a reload in the WordPress admin), the tab's session copy for this address is used.
 *
 * @since TBD
 */
export function readTabState(screen: ListTableScreenKey): ListTableTabState | undefined {
    const tabState = currentState().giveListTable;

    if (tabState?.screen === screen && typeof tabState.search === 'string') {
        return tabState;
    }

    const search = readSearchCopy(screen);

    return search ? {screen, search} : undefined;
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
    tabState: ListTableTabState,
    {searchOnly = false}: {searchOnly?: boolean} = {}
) {
    const now = Date.now();
    const collapse = searchOnly && now - lastSearchChangeAt < SEARCH_COLLAPSE_MS;

    lastSearchChangeAt = searchOnly ? now : -Infinity;
    write(collapse ? 'replaceState' : 'pushState', parameters, options, tabState);
}

/**
 * Replaces the current history entry, for corrections and legacy rewrites. It adds no entry.
 *
 * @since TBD
 */
export function replaceEntry(
    parameters: Record<string, string>,
    options: HistoryWriteOptions,
    tabState: ListTableTabState
) {
    write('replaceState', parameters, options, tabState);
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
