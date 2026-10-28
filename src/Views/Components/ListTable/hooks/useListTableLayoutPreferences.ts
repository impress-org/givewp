import {useCallback, useEffect, useRef} from 'react';
import {useDispatch} from '@wordpress/data';
import {__} from '@wordpress/i18n';
import '../../AdminDetailsPage/store';
import {clearLayout, saveLayout} from '../state/layoutApi';
import {isDefaultLayout as showsDefaultLayout, toStoredLayout} from '../state/storedLayout';
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
    const pending = useRef<ListTableView | null>(null);
    const timer = useRef<ReturnType<typeof setTimeout>>();
    const hasNotified = useRef(false);
    currentView.current = view;

    const send = (target: ListTableView, keepalive: boolean) => {
        if (!screenKey) {
            return;
        }

        const record = toStoredLayout(target, defaults);
        const request = showsDefaultLayout(target, defaults)
            ? clearLayout(screenKey, {keepalive})
            : saveLayout(screenKey, record, {keepalive});

        request.catch(() => {
            if (hasNotified.current) {
                return;
            }

            hasNotified.current = true;
            addNotice({
                id: ERROR_NOTICE_ID,
                type: 'error',
                isDismissible: true,
                content: __(
                    'Give could not save your table layout. Your changes stay until you leave this page.',
                    'give'
                ),
            });
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
        previousLayout.current = layoutKey;

        if (!screenKey || !changed || origin !== 'user') {
            return;
        }

        pending.current = view;
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
        replaceView(next, 'reset');
        send(next, false);
    }, [defaults, replaceView, screenKey]);

    return {resetLayout, isDefaultLayout: showsDefaultLayout(view, defaults)};
}
