import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {DataViewsListTableProps} from '../DataViewsListTable';
import type {ListTablePageProps, ListTableResponse} from './types';
import ListTablePage from './index';

jest.mock('@givewp/components/AdminDetailsPage/Notifications', () => () => null);
jest.mock('./ListTablePageHeader', () => () => null);

const mockRenderedTable = jest.fn<null, [DataViewsListTableProps]>(() => null);
jest.mock('../DataViewsListTable', () => (props: DataViewsListTableProps) => mockRenderedTable(props));

type QueryState = {
    data: ListTableResponse | undefined;
    error: Error | undefined;
    isLoading: boolean;
    isValidating: boolean;
    refresh: () => Promise<unknown>;
};

let mockListQuery: QueryState;
const mockIdle: QueryState = {
    data: undefined,
    error: undefined,
    isLoading: false,
    isValidating: false,
    refresh: () => Promise.resolve(),
};
jest.mock('../hooks/useListTableQuery', () => ({
    __esModule: true,
    default: (endpoint: string | null) => (endpoint === null ? mockIdle : mockListQuery),
}));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const props: ListTablePageProps = {
    title: 'Donations',
    apiSettings: {
        apiRoot: 'https://example.test/wp-json/give-api/v2/admin/donations',
        apiNonce: 'nonce',
        table: {columns: [{id: 'id', label: 'ID', sortable: true}]},
    },
    filterSettings: [{name: 'search', type: 'search'}],
    listTableBlankSlate: null,
};

const result = (totalItems: number): ListTableResponse =>
    ({items: [], totalItems, totalPages: 1} as unknown as ListTableResponse);

let root: Root;
let container: HTMLDivElement;

const render = () => act(() => root.render(<ListTablePage {...props} />));
const lastTable = (): DataViewsListTableProps =>
    mockRenderedTable.mock.calls[mockRenderedTable.mock.calls.length - 1][0];
const status = () => container.querySelector('[role="status"]')?.textContent;
const wait = (ms: number) => act(() => jest.advanceTimersByTime(ms));

beforeEach(() => {
    jest.useFakeTimers();
    window.history.replaceState(null, '', '/wp-admin/edit.php?page=give-payment-history');
    container = document.createElement('div');
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    jest.useRealTimers();
});

describe('ListTablePage result count announcement', () => {
    it('waits for a cached result to finish revalidating before it announces the count', async () => {
        mockListQuery = {...mockIdle, data: result(40)};
        await render();
        await wait(1000);

        expect(status()).toBe('');

        // A view fetched before comes back from the cache while its request is still running.
        mockListQuery = {...mockIdle, data: result(12), isValidating: true};
        await act(() => lastTable().onChangeView({...lastTable().view, search: 'spring'}));
        await wait(1000);

        expect(status()).toBe('');

        mockListQuery = {...mockIdle, data: result(15)};
        await render();
        await wait(300);

        expect(status()).toBe('15 items found');

        await wait(1000);

        expect(status()).toBe('15 items found');
    });
});
