import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {ListTablePageProps, ListTableResponse} from './types';
import ListTablePage from './index';

jest.mock('@givewp/components/AdminDetailsPage/Notifications', () => () => null);
jest.mock('./ListTablePageHeader', () => () => null);
jest.mock('../DataViewsListTable', () => () => <div data-testid="table" />);
jest.mock('../filters/siteDate', () => ({
    ...jest.requireActual('../filters/siteDate'),
    siteToday: () => '2026-11-02',
}));
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
    title: 'Donations',
    apiSettings: {
        apiRoot: 'https://example.test/wp-json/give-api/v2/admin/donations',
        apiNonce: 'nonce',
        table: {columns: [{id: 'id', label: 'ID', sortable: true}]},
    },
    filterSettings: [{name: 'search', type: 'search'}],
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

describe('ListTablePage notice area', () => {
    it('comes before the table, and puts no recommendation row in a tbody', async () => {
        await act(() =>
            root.render(
                <ListTablePage
                    {...props}
                    notices={[{id: 'a', kind: 'system', title: 'Heads up', message: 'Something.'}]}
                    recommendations={{
                        options: [
                            {
                                enum: 'givewp_donations_recurring_recommendation_dismissed',
                                documentationPage: 'https://example.test/r',
                                message: 'Tip message',
                                innerHtml: 'Learn more',
                            },
                        ],
                        dismissed: [],
                        apiNonce: 'n',
                    }}
                />
            )
        );

        const table = container.querySelector('[data-testid="table"]') as HTMLElement;
        const groups = Array.from(container.querySelectorAll('[role="group"]'));

        expect(groups).toHaveLength(2);
        for (const group of groups) {
            expect(group.compareDocumentPosition(table) & Node.DOCUMENT_POSITION_FOLLOWING).toBeTruthy();
        }
        expect(container.querySelector('tbody')).toBeNull();
    });

    it('keeps the deprecated productRecommendation last, in its old wrapper', async () => {
        await act(() =>
            root.render(
                <ListTablePage
                    {...props}
                    notices={[{id: 'a', kind: 'system', title: 'Heads up', message: 'Something.'}]}
                    productRecommendation={
                        <tr>
                            <td>old tip</td>
                        </tr>
                    }
                />
            )
        );

        const old = container.querySelector('table.givewp-list-table-recommendation') as HTMLElement;
        const group = container.querySelector('[role="group"]') as HTMLElement;
        expect(old.textContent).toBe('old tip');
        expect(group.compareDocumentPosition(old) & Node.DOCUMENT_POSITION_FOLLOWING).toBeTruthy();
    });
});
