import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {ListTablePageProps, ListTableResponse} from './types';
import ListTablePage from './index';

jest.mock('@givewp/components/AdminDetailsPage/Notifications', () => () => null);
jest.mock('../DataViewsListTable', () => () => <div data-testid="table" />);
jest.mock('../filters/siteDate', () => ({
    ...jest.requireActual('../filters/siteDate'),
    siteToday: () => '2026-11-02',
}));
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
jest.mock('../hooks/useListTableStats', () => ({
    __esModule: true,
    default: () => ({stats: undefined, status: 'loading', refresh: () => Promise.resolve()}),
}));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const props: ListTablePageProps = {
    title: 'Donations',
    apiSettings: {
        apiRoot: 'https://example.test/wp-json/give-api/v2/admin/donations',
        apiNonce: 'nonce',
        table: {columns: [{id: 'id', label: 'ID', sortable: true}]},
    },
    filters: [{kind: 'search', id: 'search', label: 'Search', hint: '', parameter: 'search'}],
    testModeToggle: {label: 'Test', ariaLabel: 'Test mode'},
    statsConfig: {donationsCount: {label: 'Total Donations'}},
    notices: [{id: 'a', kind: 'system', title: 'Heads up', message: 'Something.'}],
    listTableBlankSlate: null,
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

describe('ListTablePage layout', () => {
    // The narrow layout stacks the page in DOM order, so the order of the regions in the DOM is the
    // order an admin sees on a phone (FR-031).
    it('puts the regions in the order header, test-mode toggle, tiles, notice area, list', async () => {
        await act(() => root.render(<ListTablePage {...props} />));

        const regions = [
            container.querySelector('header'),
            container.querySelector('[role="switch"]'),
            container.querySelector('.givewp-stat-tiles'),
            container.querySelector('.givewp-list-table-notice-area'),
            container.querySelector('[data-testid="table"]'),
        ];

        regions.forEach((region) => expect(region).not.toBeNull());
        regions.slice(1).forEach((region, index) => {
            expect(regions[index].compareDocumentPosition(region) & Node.DOCUMENT_POSITION_FOLLOWING).toBeTruthy();
        });
    });

    it('keeps the title before the header buttons', async () => {
        await act(() =>
            root.render(
                <ListTablePage {...props}>
                    <a className="button button-primary" href="#new">
                        New
                    </a>
                </ListTablePage>
            )
        );

        const title = container.querySelector('header h1') as HTMLElement;
        const button = container.querySelector('header .button-primary') as HTMLElement;

        expect(title.compareDocumentPosition(button) & Node.DOCUMENT_POSITION_FOLLOWING).toBeTruthy();
    });
});
