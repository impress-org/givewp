import {useCallback, useEffect, useRef, useState} from 'react';
import {useDispatch} from '@wordpress/data';
import {__} from '@wordpress/i18n';
import '../../AdminDetailsPage/store';
import {clearLayout, readStoredLayout, saveLayout} from '../state/layoutApi';
import {applyStoredLayout, isDefaultLayout as showsDefaultLayout, toStoredLayout} from '../state/storedLayout';
import type {ListTableScreen, ListTableView, ScreenDefaults} from '../state/types';
import type {ViewChangeOrigin} from './useListTableView';

const WRITE_DELAY = 1000;
const ERROR_NOTICE_ID = 'givewp-list-table-layout-error';

/**
 * @since TBD
 */
export type LayoutPreferencesOptions = {
    /** Without a screen config nothing is stored. */
    screen?: ListTableScreen;
    defaults: ScreenDefaults;
    view: ListTableView;
    origin: ViewChangeOrigin;
    replaceView: (view: ListTableView, origin: 'reset') => void;
};

/**
 * @since TBD
 */
export type LayoutPreferences = {
    /** Puts the columns, density and page size back to the screen defaults and clears the stored record. */
    resetLayout: () => void;
    isDefaultLayout: boolean;
};

/**
 * Whether the failed-write notice was shown in this page view. Kept outside the hook so a remounted
 * table (an embedded tab opened again) does not show it twice.
 */
let hasNotified = false;

/**
 * Writes run one after another, so an older write never finishes after a newer one.
 */
let writes: Promise<unknown> = Promise.resolve();

/**
 * Starts a new page view for the notice and the write queue. For tests.
 *
 * @since TBD
 */
export function resetLayoutPreferencesState(): void {
    hasNotified = false;
    writes = Promise.resolve();
}

const layoutKeyOf = ({fields, layout, perPage}: ListTableView) => JSON.stringify([fields, layout?.density, perPage]);

/**
 * Stores the user's layout (visible columns, their order, density and page size) for the screen. Only a
 * user change to the layout is written, one second after the last change, or at once when the page is
 * left. A failed write keeps the change on screen and shows one notice per page view; the next write
 * sends the whole record again. A layout equal to the defaults removes the record.
 *
 * @since TBD
 */
export default function useListTableLayoutPreferences({
    screen,
    defaults,
    view,
    origin,
    replaceView,
}: LayoutPreferencesOptions): LayoutPreferences {
    const {addNotice} = useDispatch('givewp/admin-details-page-notifications');
    const screenKey = screen?.key;
    const currentView = useRef(view);
    const previousLayout = useRef(layoutKeyOf(view));
    const previousPerPage = useRef(view.perPage);
    // The page size the user chose for this screen. A size that only came from the address is not part of the layout.
    const [layoutPerPage, setLayoutPerPage] = useState(
        () =>
            (screenKey ? applyStoredLayout(defaults, readStoredLayout(screenKey)).perPage : defaults.perPage) ??
            defaults.perPage
    );
    const layoutPerPageRef = useRef(layoutPerPage);
    layoutPerPageRef.current = layoutPerPage;
    const pending = useRef<ListTableView | null>(null);
    const timer = useRef<ReturnType<typeof setTimeout>>();
    currentView.current = view;

    const send = (target: ListTableView, keepalive: boolean) => {
        if (!screenKey) {
            return;
        }

        const record = toStoredLayout(target, defaults);
        const write = () =>
            (showsDefaultLayout(target, defaults)
                ? clearLayout(screenKey, {keepalive})
                : saveLayout(screenKey, record, {keepalive})
            ).catch(notifyOnce);

        // The page is closing: send now, the queue may never get to it.
        writes = keepalive ? write() : writes.then(write);
    };

    const notifyOnce = () => {
        if (hasNotified) {
            return;
        }

        hasNotified = true;
        addNotice({
            id: ERROR_NOTICE_ID,
            type: 'error',
            isDismissible: true,
            content: __('Give could not save your table layout. Your changes stay until you leave this page.', 'give'),
        });
    };

    const cancel = () => {
        clearTimeout(timer.current);
        timer.current = undefined;
        pending.current = null;
    };

    const flush = (keepalive: boolean) => {
        const target = pending.current;
        cancel();

        if (target) {
            send(target, keepalive);
        }
    };

    const flushRef = useRef(flush);
    flushRef.current = flush;

    useEffect(() => {
        const layoutKey = layoutKeyOf(view);
        const changed = layoutKey !== previousLayout.current;
        const perPageChanged = view.perPage !== previousPerPage.current;
        previousLayout.current = layoutKey;
        previousPerPage.current = view.perPage;

        if (!screenKey || !changed || origin !== 'user') {
            return;
        }

        const nextPerPage = perPageChanged ? view.perPage : layoutPerPageRef.current;
        setLayoutPerPage(nextPerPage);
        pending.current = {...view, perPage: nextPerPage};
        clearTimeout(timer.current);
        timer.current = setTimeout(() => flushRef.current(false), WRITE_DELAY);
    }, [view]);

    useEffect(() => {
        if (!screenKey) {
            return undefined;
        }

        const onPageHide = () => flushRef.current(true);
        window.addEventListener('pagehide', onPageHide);

        return () => {
            window.removeEventListener('pagehide', onPageHide);
            flushRef.current(false);
        };
    }, [screenKey]);

    const resetLayout = useCallback(() => {
        const current = currentView.current;
        const next: ListTableView = {
            ...current,
            fields: defaults.fields,
            layout: {...current.layout, density: defaults.density},
            perPage: defaults.perPage,
            page: current.perPage === defaults.perPage ? current.page : 1,
        };

        cancel();
        setLayoutPerPage(defaults.perPage);
        replaceView(next, 'reset');
        send(next, false);
    }, [defaults, replaceView, screenKey]);

    return {resetLayout, isDefaultLayout: showsDefaultLayout({...view, perPage: layoutPerPage}, defaults)};
}
