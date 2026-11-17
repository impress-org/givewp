import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import apiFetch from '@wordpress/api-fetch';
import type {View} from '@wordpress/dataviews';
import {SWRConfig} from 'swr';
import type {DataViewsListTableProps} from '../DataViewsListTable';
import type {ListTablePageProps} from './types';
import ListTablePage from './index';

jest.mock('@wordpress/api-fetch', () => jest.fn());
jest.mock('@givewp/components/AdminDetailsPage/Notifications', () => () => null);
jest.mock('./ListTablePageHeader', () => () => null);

const mockRenderedTable = jest.fn<null, [DataViewsListTableProps]>(() => null);
jest.mock('../DataViewsListTable', () => (props: DataViewsListTableProps) => mockRenderedTable(props));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockedFetch = apiFetch as unknown as jest.Mock;
const API_ROOT = 'https://example.test/wp-json/give-api/v2/admin/donations';
const PATH = '/wp-admin/edit.php?page=give-payment-history';

const props: ListTablePageProps = {
    title: 'Donations',
    apiSettings: {
        apiRoot: API_ROOT,
        apiNonce: 'nonce',
        table: {columns: [{id: 'id', label: 'ID', sortable: true}]},
    },
    filters: [{kind: 'search', id: 'search', label: 'Search donations', hint: '', parameter: 'search'}],
    listTableBlankSlate: null,
    screen: {key: 'donations'},
};

type Deferred = {resolve: (value: unknown) => void; reject: (error: Error) => void};
type Pending = {url: URL; deferred: Deferred};

let pending: Pending[];
let root: Root;
let container: HTMLDivElement;

const rows = (extra: object = {}) => ({items: [{id: 1}], hasNextPage: true, ...extra});

const isCount = (url: URL) => url.pathname.endsWith('/count');
const rowRequests = () => pending.filter(({url}) => !isCount(url));
const countRequests = () => pending.filter(({url}) => isCount(url));

const lastTable = (): DataViewsListTableProps =>
    mockRenderedTable.mock.calls[mockRenderedTable.mock.calls.length - 1][0];

async function answer(request: Pending | undefined, value: unknown) {
    await act(async () => request?.deferred.resolve(value));
}

async function render() {
    container = document.createElement('div');
    root = createRoot(container);

    await act(async () => {
        root.render(
            <SWRConfig value={{provider: () => new Map(), dedupingInterval: 0}}>
                <ListTablePage {...props} />
            </SWRConfig>
        );
    });
}

const changeView = (change: (view: View) => View) =>
    act(async () => lastTable().onChangeView(change(lastTable().view)));

beforeEach(() => {
    pending = [];
    mockRenderedTable.mockClear();
    window.history.replaceState(null, '', PATH);
    mockedFetch.mockReset();
    mockedFetch.mockImplementation(
        ({url}: {url: string}) =>
            new Promise((resolve, reject) => {
                pending.push({url: new URL(url), deferred: {resolve, reject}});
            })
    );
});

afterEach(() => act(() => root.unmount()));

describe('ListTablePage totals', () => {
    it('asks for the rows without the count, and for the count with the same filters but no page or sort', async () => {
        window.history.replaceState(null, '', `${PATH}&give-search=smith&give-paged=3&give-orderby=id&give-order=asc`);
        await render();

        expect(rowRequests()).toHaveLength(1);
        expect(countRequests()).toHaveLength(1);

        const rowParams = Object.fromEntries(rowRequests()[0].url.searchParams);
        const countParams = Object.fromEntries(countRequests()[0].url.searchParams);

        expect(rowParams).toMatchObject({withCount: 'false', page: '3', search: 'smith', sortColumn: 'id'});
        expect(countParams).toMatchObject({search: 'smith', perPage: rowParams.perPage});
        expect(countParams).not.toHaveProperty('page');
        expect(countParams).not.toHaveProperty('sortColumn');
        expect(countParams).not.toHaveProperty('sortDirection');
        expect(countParams).not.toHaveProperty('withCount');
    });

    it('shows the rows and the arrows before the count arrives, with no total', async () => {
        await render();

        expect(lastTable().paginationInfo).toBeNull();
        expect(lastTable().hasNextPage).toBe(true);

        await answer(rowRequests()[0], rows({hasNextPage: false}));

        expect(lastTable().data).toEqual([{id: 1}]);
        expect(lastTable().paginationInfo).toBeNull();
        expect(lastTable().hasNextPage).toBe(false);
    });

    it('loads the page typed in the address right away, without waiting for the count', async () => {
        window.history.replaceState(null, '', `${PATH}&give-paged=40`);
        await render();

        expect(rowRequests()[0].url.searchParams.get('page')).toBe('40');

        await answer(rowRequests()[0], rows());

        expect(lastTable().view.page).toBe(40);
        expect(lastTable().data).toEqual([{id: 1}]);
    });

    it('shows the total when the count arrives', async () => {
        await render();
        await answer(rowRequests()[0], rows());
        await answer(countRequests()[0], {totalItems: 464, totalPages: 16});

        expect(lastTable().paginationInfo).toEqual({totalItems: 464, totalPages: 16});
    });

    it('does not count again when only the page changes', async () => {
        await render();
        await answer(rowRequests()[0], rows());
        await answer(countRequests()[0], {totalItems: 464, totalPages: 16});
        await changeView((view) => ({...view, page: 2}));

        expect(rowRequests()).toHaveLength(2);
        expect(countRequests()).toHaveLength(1);
        expect(lastTable().paginationInfo).toEqual({totalItems: 464, totalPages: 16});
    });

    it('ignores a count that arrives for filters the admin has already changed', async () => {
        await render();
        await answer(rowRequests()[0], rows());
        const stale = countRequests()[0];

        await changeView((view) => ({...view, search: 'jones'}));

        expect(countRequests()).toHaveLength(2);

        await answer(stale, {totalItems: 464, totalPages: 16});

        expect(lastTable().paginationInfo).toBeNull();

        await answer(countRequests()[1], {totalItems: 3, totalPages: 1});

        expect(lastTable().paginationInfo).toEqual({totalItems: 3, totalPages: 1});
    });

    it('does not show the old total while a new filter counts', async () => {
        await render();
        await answer(rowRequests()[0], rows());
        await answer(countRequests()[0], {totalItems: 464, totalPages: 16});
        await changeView((view) => ({...view, search: 'jones'}));

        expect(lastTable().paginationInfo).toBeNull();
    });

    it('keeps the table and the arrows when the count fails', async () => {
        await render();
        await answer(rowRequests()[0], rows());
        await act(async () => countRequests()[0].deferred.reject(new Error('nope')));

        expect(lastTable().paginationInfo).toBeNull();
        expect(lastTable().hasError).toBe(false);
        expect(lastTable().data).toEqual([{id: 1}]);
    });

    it('uses the totals of the rows response when it has them', async () => {
        await render();
        await answer(rowRequests()[0], rows({totalItems: '12', totalPages: 1}));

        expect(lastTable().paginationInfo).toEqual({totalItems: 12, totalPages: 1});
    });

    it('moves a page above the counted total to the last page', async () => {
        window.history.replaceState(null, '', `${PATH}&give-paged=40`);
        await render();
        await answer(rowRequests()[0], rows());
        await answer(countRequests()[0], {totalItems: 464, totalPages: 16});

        expect(lastTable().view.page).toBe(16);
    });
});
