import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import apiFetch from '@wordpress/api-fetch';
import {dispatch, select} from '@wordpress/data';
import type {ListTableScreen, ListTableView, ScreenDefaults} from '../state/types';
import type {ViewChangeOrigin} from './useListTableView';
import useListTableLayoutPreferences, {
    type LayoutPreferences,
    resetLayoutPreferencesState,
} from './useListTableLayoutPreferences';

jest.mock('@wordpress/api-fetch', () => jest.fn());

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockedFetch = apiFetch as unknown as jest.Mock;
const NOTICES = 'givewp/admin-details-page-notifications';
const layoutNotices = () =>
    select(NOTICES)
        .getNotifications()
        .filter((notice: {id: string}) => notice.id === 'givewp-list-table-layout-error');

const screen: ListTableScreen = {key: 'donations'};
const defaults: ScreenDefaults = {
    fields: ['donor', 'amount'],
    fieldOrder: ['id', 'donor', 'amount', 'form'],
    density: 'balanced',
    perPage: 30,
    sort: {field: 'id', direction: 'desc'},
    lockedFields: ['id'],
    filters: [],
};
const defaultView: ListTableView = {
    type: 'table',
    page: 1,
    perPage: 30,
    fields: ['donor', 'amount'],
    layout: {density: 'balanced'},
};

type Props = {view: ListTableView; origin: ViewChangeOrigin; screen?: ListTableScreen};

let root: Root;
let latest: LayoutPreferences;
const replaceView = jest.fn();

function Harness({view, origin, screen: currentScreen}: Props) {
    latest = useListTableLayoutPreferences({screen: currentScreen, defaults, view, origin, replaceView});

    return null;
}

const render = (props: Props) => act(() => root.render(<Harness {...props} />));
const change = (view: Partial<ListTableView>, origin: ViewChangeOrigin = 'user') =>
    render({view: {...defaultView, ...view}, origin, screen});
const advance = (ms: number) => act(async () => jest.advanceTimersByTime(ms));
const requests = () => mockedFetch.mock.calls.map(([options]) => options);

beforeEach(() => {
    jest.useFakeTimers();
    mockedFetch.mockReset();
    mockedFetch.mockResolvedValue({});
    replaceView.mockReset();
    resetLayoutPreferencesState();
    dispatch(NOTICES).dismissNotification('givewp-list-table-layout-error');
    root = createRoot(document.createElement('div'));
});

afterEach(() => {
    act(() => root.unmount());
    jest.useRealTimers();
});

describe('useListTableLayoutPreferences', () => {
    it('sends one PUT with only the differences 1 s after a burst of changes', async () => {
        await render({view: defaultView, origin: 'load', screen});

        await change({layout: {density: 'compact'}});
        await advance(500);
        await change({layout: {density: 'compact'}, fields: ['amount']});
        await advance(999);

        expect(mockedFetch).not.toHaveBeenCalled();

        await advance(1);

        expect(requests()).toEqual([
            {
                path: '/givewp/v3/list-table-layouts/donations',
                method: 'PUT',
                data: {version: 1, visibility: {donor: false}, density: 'compact'},
                keepalive: false,
            },
        ]);
    });

    it('sends at once with keepalive on pagehide', async () => {
        await render({view: defaultView, origin: 'load', screen});
        await change({perPage: 50});

        await act(async () => window.dispatchEvent(new Event('pagehide')));

        expect(requests()).toEqual([expect.objectContaining({method: 'PUT', keepalive: true})]);

        await advance(1000);

        expect(mockedFetch).toHaveBeenCalledTimes(1);
    });

    it('keeps the change on screen, shows one notice per page view, and sends the full record next', async () => {
        mockedFetch.mockRejectedValue(new Error('offline'));
        await render({view: defaultView, origin: 'load', screen});

        await change({fields: ['amount']});
        await advance(1000);

        expect(replaceView).not.toHaveBeenCalled();
        expect(layoutNotices()).toEqual([
            expect.objectContaining({type: 'error', isDismissible: true, notificationType: 'notice'}),
        ]);

        await act(async () => dispatch(NOTICES).dismissNotification('givewp-list-table-layout-error'));
        await change({fields: ['amount'], layout: {density: 'compact'}});
        await advance(1000);

        expect(layoutNotices()).toEqual([]);
        expect(requests()[1].data).toEqual({version: 1, visibility: {donor: false}, density: 'compact'});
    });

    it('does not show the notice again when the table is mounted again', async () => {
        mockedFetch.mockRejectedValue(new Error('offline'));
        await render({view: defaultView, origin: 'load', screen});
        await change({fields: ['amount']});
        await advance(1000);
        await act(async () => dispatch(NOTICES).dismissNotification('givewp-list-table-layout-error'));

        act(() => root.unmount());
        root = createRoot(document.createElement('div'));
        await render({view: defaultView, origin: 'load', screen});
        await change({layout: {density: 'compact'}});
        await advance(1000);

        expect(mockedFetch).toHaveBeenCalledTimes(2);
        expect(layoutNotices()).toEqual([]);
    });

    it('starts a write only after the one before it has finished', async () => {
        let finishFirst: (value: unknown) => void = () => undefined;
        mockedFetch.mockReturnValueOnce(new Promise((resolve) => (finishFirst = resolve)));
        await render({view: defaultView, origin: 'load', screen});

        await change({fields: ['amount']});
        await advance(1000);
        await change({fields: ['amount'], layout: {density: 'compact'}});
        await advance(1000);

        expect(mockedFetch).toHaveBeenCalledTimes(1);

        await act(async () => finishFirst({}));

        expect(requests().map(({data}) => data)).toEqual([
            {version: 1, visibility: {donor: false}},
            {version: 1, visibility: {donor: false}, density: 'compact'},
        ]);
    });

    it('sends DELETE when the layout is back to the defaults', async () => {
        await render({view: {...defaultView, layout: {density: 'compact'}}, origin: 'load', screen});
        await change({layout: {density: 'balanced'}});
        await advance(1000);

        expect(requests()).toEqual([expect.objectContaining({method: 'DELETE'})]);
    });

    it('writes nothing for a change that is not from the user, or that leaves the layout as it is', async () => {
        await render({view: defaultView, origin: 'load', screen});
        await change({fields: ['amount']}, 'history');
        await change({fields: ['amount'], perPage: 50}, 'correction');
        await change({fields: ['amount'], perPage: 50, page: 3});
        await advance(1000);

        expect(mockedFetch).not.toHaveBeenCalled();
    });

    it('does nothing without a screen config', async () => {
        await render({view: defaultView, origin: 'load'});
        await render({view: {...defaultView, fields: ['amount']}, origin: 'user'});
        await advance(1000);
        await act(async () => window.dispatchEvent(new Event('pagehide')));

        expect(mockedFetch).not.toHaveBeenCalled();
    });

    it('does not count a page size that only came from the address as a changed layout', async () => {
        await render({view: {...defaultView, perPage: 10}, origin: 'load', screen});

        expect(latest.isDefaultLayout).toBe(true);
    });

    it('keeps an address page size out of the stored record when another part of the layout changes', async () => {
        await render({view: {...defaultView, perPage: 10}, origin: 'load', screen});

        await change({perPage: 10, fields: ['amount']});
        await advance(1000);

        expect(requests()).toEqual([
            expect.objectContaining({method: 'PUT', data: {version: 1, visibility: {donor: false}}}),
        ]);
    });

    it('counts a page size the user picked as part of the layout', async () => {
        await render({view: defaultView, origin: 'load', screen});

        await change({perPage: 50});
        await advance(1000);

        expect(latest.isDefaultLayout).toBe(false);
        expect(requests()).toEqual([expect.objectContaining({method: 'PUT', data: {version: 1, perPage: 50}})]);
    });

    it('resets the layout to the defaults and clears the stored record', async () => {
        await render({view: {...defaultView, fields: ['amount'], page: 2}, origin: 'load', screen});

        expect(latest.isDefaultLayout).toBe(false);

        await act(async () => latest.resetLayout());

        expect(replaceView).toHaveBeenCalledWith({...defaultView, page: 2}, 'reset');
        expect(requests()).toEqual([expect.objectContaining({method: 'DELETE'})]);
    });

    it('keeps the search, filters and sort on reset', async () => {
        const query: Partial<ListTableView> = {
            search: 'jane',
            sort: {field: 'amount', direction: 'asc'},
            filters: [{field: 'status', operator: 'is', value: 'publish'}],
        };
        await render({view: {...defaultView, ...query, layout: {density: 'compact'}}, origin: 'load', screen});

        await act(async () => latest.resetLayout());

        expect(replaceView).toHaveBeenCalledWith({...defaultView, ...query}, 'reset');
    });
});
