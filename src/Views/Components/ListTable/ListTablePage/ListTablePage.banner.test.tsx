import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {ListTablePageProps, ListTableResponse} from './types';
import ListTablePage from './index';

jest.mock('@givewp/components/AdminDetailsPage/Notifications', () => () => null);
jest.mock('../DataViewsListTable', () => () => <div data-testid="table" />);
jest.mock('@givewp/promotions/requests/dismissRecommendation', () => ({__esModule: true, default: jest.fn()}));
jest.mock('../hooks/useListTableQuery', () => ({
    __esModule: true,
    default: () => ({
        data: {items: [], totalItems: 0, totalPages: 1} as unknown as ListTableResponse,
        error: undefined,
        isLoading: false,
        isValidating: false,
        refresh: () => Promise.resolve(),
    }),
}));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const props: ListTablePageProps = {
    title: 'Donation Forms',
    apiSettings: {
        apiRoot: 'https://example.test/wp-json/give-api/v2/admin/forms',
        apiNonce: 'nonce',
        table: {columns: [{id: 'id', label: 'ID', sortable: true}]},
    },
    filterSettings: [{name: 'search', type: 'search'}],
    listTableBlankSlate: null,
    banner: () => <p data-testid="banner">Welcome</p>,
};

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('ListTablePage banner', () => {
    it('renders in its own banner landmark under the header, outside the list content', async () => {
        await act(() => root.render(<ListTablePage {...props} />));

        const section = container.querySelector('section[role="banner"]') as HTMLElement;
        const header = container.querySelector('header') as HTMLElement;
        const table = container.querySelector('[data-testid="table"]') as HTMLElement;

        expect(section.querySelector('[data-testid="banner"]')).not.toBeNull();
        expect(header.compareDocumentPosition(section) & Node.DOCUMENT_POSITION_FOLLOWING).toBeTruthy();
        expect(section.compareDocumentPosition(table) & Node.DOCUMENT_POSITION_FOLLOWING).toBeTruthy();
        expect(section.parentElement?.contains(table.parentElement)).toBe(true);
        expect(table.parentElement?.contains(section)).toBe(false);
    });

    it('is not shown in content mode, where the header is not shown either', async () => {
        await act(() => root.render(<ListTablePage {...props} contentMode />));

        expect(container.querySelector('[data-testid="banner"]')).toBeNull();
    });
});
