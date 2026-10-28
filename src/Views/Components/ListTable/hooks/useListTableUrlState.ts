import {useEffect, useRef} from 'react';
import {assembleView} from '../state/assembleView';
import {isCurrentQuery, onPopState, pushEntry, readQuery, readTabState, replaceEntry} from '../state/listTableHistory';
import {parseViewQuery, serializeViewQuery, viewQueryKeys} from '../state/viewQuery';
import type {
    ListTableScreen,
    ListTableScreenKey,
    ListTableView,
    ParseResult,
    ScreenDefaults,
    ViewQueryContext,
} from '../state/types';
import type {ViewChangeOrigin} from './useListTableView';

/**
 * The view parts a standalone screen reads on load: the parsed address and the tab's search term.
 *
 * @since TBD
 */
export type UrlState = ParseResult & {search: string};

/**
 * The key of a screen that keeps its view in the URL: a screen with a config that is not embedded
 * (`contentMode`). Embedded tables never read or write the address or the tab state.
 *
 * @since TBD
 */
export function urlScreenKey(
    screen: ListTableScreen | undefined,
    contentMode: boolean
): ListTableScreenKey | undefined {
    return contentMode ? undefined : screen?.key;
}

/**
 * Reads the address and the tab state once, before the first render, so the first list request
 * already carries them.
 *
 * @since TBD
 */
export function readUrlState(screenKey: ListTableScreenKey, context: ViewQueryContext): UrlState {
    return {
        ...parseViewQuery(readQuery(), context),
        search: readTabState(screenKey)?.search ?? '',
    };
}

/**
 * What a standalone table without a screen config reads on load: only the filters named in the address
 * (links such as `?donor=ID` that were built before the view moved into the URL). It never writes the
 * address, and ignores the page, sort and page size.
 *
 * @since TBD
 */
export function readLegacyUrlState(context: ViewQueryContext): UrlState {
    const {query} = parseViewQuery(readQuery(), context);

    return {query: {filters: query.filters}, corrected: false, legacyKeys: [], search: ''};
}

/**
 * @since TBD
 */
export type ListTableUrlStateOptions = {
    screen?: ListTableScreen;
    /** Embedded tables never touch the address. */
    contentMode: boolean;
    context: ViewQueryContext;
    defaults: ScreenDefaults;
    /** What `readUrlState` read on load. */
    initial?: UrlState;
    view: ListTableView;
    origin: ViewChangeOrigin;
    replaceView: (view: ListTableView, origin: 'history') => void;
};

const sameParameters = (a: Record<string, string>, b: Record<string, string>) =>
    JSON.stringify(a) === JSON.stringify(b);

/**
 * Keeps a standalone screen's view in the address (`give-` parameters) and its search term in the
 * tab's history entry. A user change adds an entry, a correction replaces it, and back or forward
 * re-assembles the view with the current columns and density and writes nothing.
 *
 * @since TBD
 */
export default function useListTableUrlState({
    screen,
    contentMode,
    context,
    defaults,
    initial,
    view,
    origin,
    replaceView,
}: ListTableUrlStateOptions) {
    const previousView = useRef(view);
    const currentView = useRef(view);

    // Kept after each commit, so the popstate listener never reads the view of an abandoned render.
    useEffect(() => {
        currentView.current = view;
    });

    const screenKey = urlScreenKey(screen, contentMode);
    const owned = viewQueryKeys(context);
    const tabState = (key: ListTableScreenKey, target: ListTableView) => ({screen: key, search: target.search ?? ''});

    // On load: rewrite an address that was corrected, used legacy names, or is not in canonical form.
    useEffect(() => {
        if (!screenKey) {
            return;
        }

        const parameters = serializeViewQuery(view, context);
        const options = {owned, legacyKeys: initial?.legacyKeys ?? []};

        if (initial?.corrected || !isCurrentQuery(parameters, options)) {
            replaceEntry(parameters, options, tabState(screenKey, view));
        }
    }, []);

    useEffect(() => {
        const previous = previousView.current;
        previousView.current = view;

        if (!screenKey || previous === view || origin === 'history' || origin === 'load') {
            return;
        }

        const parameters = serializeViewQuery(view, context);

        if (origin === 'correction') {
            replaceEntry(parameters, {owned}, tabState(screenKey, view));
            return;
        }

        const queryChanged = !sameParameters(serializeViewQuery(previous, context), parameters);
        const searchChanged = (previous.search ?? '') !== (view.search ?? '');

        if (queryChanged || searchChanged) {
            pushEntry(parameters, {owned}, tabState(screenKey, view), {searchOnly: !queryChanged});
        }
    }, [view]);

    useEffect(() => {
        if (!screenKey) {
            return undefined;
        }

        return onPopState(() => {
            const {fields, layout} = currentView.current;
            const {query} = parseViewQuery(readQuery(), context);

            replaceView(
                assembleView({
                    defaults,
                    // Columns and density stay as they are; the page size comes from the address or the start.
                    layout: {
                        fields: fields ?? defaults.fields,
                        density: layout?.density ?? defaults.density,
                        perPage: defaults.perPage,
                    },
                    query,
                    search: readTabState(screenKey)?.search ?? '',
                }),
                'history'
            );
        });
    }, [screenKey]);
}
