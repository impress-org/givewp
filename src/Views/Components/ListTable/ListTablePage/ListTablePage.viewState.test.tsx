import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import apiFetch from '@wordpress/api-fetch';
import type {View} from '@wordpress/dataviews';
import {SWRConfig} from 'swr';
import type {DataViewsListTableProps} from '../DataViewsListTable';
import type {FilterConfig, ListTablePageProps} from './types';
import ListTablePage from './index';

jest.mock('@wordpress/api-fetch', () => jest.fn());
jest.mock('@givewp/components/AdminDetailsPage/Notifications', () => () => null);
jest.mock('./ListTablePageHeader', () => () => null);

const mockRenderedTable = jest.fn<null, [DataViewsListTableProps]>(() => null);
jest.mock('../DataViewsListTable', () => (props: DataViewsListTableProps) => mockRenderedTable(props));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockedFetch = apiFetch as unknown as jest.Mock;
const API_ROOT = 'https://example.test/wp-json/give-api/v2/admin/donations';
const PATH = '/wp-admin/edit.php';

const filterSettings: FilterConfig[] = [
    {name: 'search', type: 'search'},
    {name: 'donor', type: 'hidden'},
    {
        name: 'filterBy',
        type: 'filterby',
        groupedOptions: [
            {
                id: 'status',
                apiParam: 'status',
                name: 'Status',
                type: 'checkbox',
                options: [
                    {text: 'Failed', value: 'failed'},
                    {text: 'Pending', value: 'pending'},
                ],
            },
        ],
    },
];

const props = (overrides: Partial<ListTablePageProps> = {}): ListTablePageProps => ({
    title: 'Donations',
    apiSettings: {
        apiRoot: API_ROOT,
        apiNonce: 'nonce',
        table: {
            columns: [
                {id: 'id', label: 'ID', sortable: true},
                {id: 'donor', label: 'Donor', sortable: false},
                {id: 'amount', label: 'Amount', sortable: true},
                {id: 'form', label: 'Form', sortable: false},
            ],
        },
    },
    filterSettings,
    listTableBlankSlate: null,
    screen: {key: 'donations', defaults: {hiddenFields: ['form'], density: 'compact'}},
    ...overrides,
});

let root: Root;
let container: HTMLDivElement;

async function render(overrides: Partial<ListTablePageProps> = {}) {
    container = document.createElement('div');
    root = createRoot(container);

    await act(async () => {
        root.render(
            <SWRConfig value={{provider: () => new Map(), dedupingInterval: 0}}>
                <ListTablePage {...props(overrides)} />
            </SWRConfig>
        );
    });
}

const listRequests = () =>
    mockedFetch.mock.calls
        .filter(([options]) => options.url)
        .map(([options]) => new URL(options.url))
        .filter((url) => url.pathname.endsWith('/donations'));

const firstView = (): View => mockRenderedTable.mock.calls[0][0].view;
const lastTable = (): DataViewsListTableProps =>
    mockRenderedTable.mock.calls[mockRenderedTable.mock.calls.length - 1][0];

async function changeView(change: (view: View) => View) {
    await act(async () => lastTable().onChangeView(change(lastTable().view)));
}

beforeEach(() => {
    mockedFetch.mockReset();
    mockedFetch.mockResolvedValue({items: [], totalItems: 300, totalPages: 10});
    mockRenderedTable.mockClear();
});

afterEach(() => act(() => root.unmount()));

describe('ListTablePage first render', () => {
    beforeEach(() => {
        window.history.replaceState(
            {giveListTable: {screen: 'donations', search: 'smith'}},
            '',
            `${PATH}?page=give-payment-history&give-status=failed,pending&give-orderby=amount&give-order=asc&give-paged=3&give-per-page=50`
        );
    });

    it("already shows the screen's fields and density on the first render", async () => {
        await render();

        expect(firstView().fields).toEqual(['id', 'amount']);
        expect(firstView().layout).toEqual({density: 'compact'});
    });

    describe('with a stored layout', () => {
        beforeEach(() => {
            window.GiveListTableLayouts = {
                path: '/givewp/v3/list-table-layouts',
                layouts: {
                    donations: {
                        version: 1,
                        visibility: {form: true, donor: false},
                        order: ['form', 'amount'],
                        density: 'comfortable',
                        perPage: 20,
                    },
                },
            };
        });

        afterEach(() => {
            delete window.GiveListTableLayouts;
        });

        it('already shows the stored fields and density on the first render', async () => {
            await render();

            expect(firstView().fields).toEqual(['id', 'form', 'amount']);
            expect(firstView().layout).toEqual({density: 'comfortable'});
        });

        it("still sends the URL's view with the first request, and no second one", async () => {
            await render();

            const requests = listRequests();

            expect(requests).toHaveLength(1);
            expect(Object.fromEntries(requests[0].searchParams)).toMatchObject({
                page: '3',
                perPage: '50',
                sortColumn: 'amount',
                sortDirection: 'asc',
                status: 'failed,pending',
                search: 'smith',
            });
        });

        it('starts with the stored page size when the URL names none', async () => {
            window.history.replaceState(null, '', `${PATH}?page=give-payment-history`);

            await render();

            expect(firstView().perPage).toBe(20);
            expect(listRequests()).toHaveLength(1);
        });

        it('keeps the stored page size on back and forward to an entry that names none', async () => {
            window.history.replaceState(null, '', `${PATH}?page=give-payment-history`);
            await render();

            window.history.pushState(null, '', `${PATH}?page=give-payment-history&give-paged=2`);
            await act(async () => window.dispatchEvent(new PopStateEvent('popstate')));

            expect(lastTable().view).toMatchObject({page: 2, perPage: 20});

            window.history.pushState(null, '', `${PATH}?page=give-payment-history&give-per-page=50`);
            await act(async () => window.dispatchEvent(new PopStateEvent('popstate')));

            expect(lastTable().view.perPage).toBe(50);
        });
    });

    it("sends the URL's page, sort, filters and page size and the tab's search with the first request", async () => {
        await render();

        const [first] = listRequests();

        expect(Object.fromEntries(first.searchParams)).toMatchObject({
            page: '3',
            perPage: '50',
            sortColumn: 'amount',
            sortDirection: 'asc',
            status: 'failed,pending',
            search: 'smith',
        });
    });

    it('makes no second request for the same view', async () => {
        await render();

        expect(listRequests()).toHaveLength(1);
    });

    it('leaves a canonical address as it is', async () => {
        const href = window.location.href;

        await render();

        expect(window.location.href).toBe(href);
    });
});

describe('ListTablePage URL state', () => {
    beforeEach(() => {
        window.history.replaceState(null, '', `${PATH}?page=give-payment-history`);
    });

    it('rewrites a legacy bare name and a bare search without a new entry', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-payment-history&donor=12&search=smith`);
        const length = window.history.length;

        await render();

        expect(window.location.search).toBe('?page=give-payment-history&give-donor=12');
        expect(window.history.length).toBe(length);
        expect(firstView().filters).toContainEqual({field: 'filter-donor', operator: 'is', value: '12'});
        expect(firstView().search).toBe('');
    });

    it('pushes one entry per user change and keeps the search out of the address', async () => {
        await render();
        const length = window.history.length;

        await changeView((view) => ({...view, sort: {field: 'amount', direction: 'asc'}}));
        await changeView((view) => ({...view, search: 'smith'}));

        expect(window.history.length).toBe(length + 2);
        expect(window.location.search).toBe('?page=give-payment-history&give-orderby=amount&give-order=asc');
        expect(window.history.state.giveListTable).toEqual({screen: 'donations', search: 'smith'});
    });

    it('writes nothing for a layout change', async () => {
        await render();
        const length = window.history.length;

        await changeView((view) => ({...view, layout: {density: 'comfortable'}}));

        expect(window.history.length).toBe(length);
    });

    it('re-assembles the view on back and forward, keeps the columns, and writes nothing', async () => {
        await render();
        await changeView((view) => ({...view, layout: {density: 'comfortable'}, fields: ['amount']}));

        window.history.pushState(
            {giveListTable: {screen: 'donations', search: 'jane'}},
            '',
            `${PATH}?page=give-payment-history&give-paged=2`
        );
        const href = window.location.href;
        const length = window.history.length;

        await act(async () => window.dispatchEvent(new PopStateEvent('popstate')));

        expect(lastTable().view).toMatchObject({
            page: 2,
            search: 'jane',
            fields: ['amount'],
            layout: {density: 'comfortable'},
        });
        expect(window.location.href).toBe(href);
        expect(window.history.length).toBe(length);
    });

    it('replaces the entry when the page is clamped to the last page', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-payment-history&give-paged=40`);
        const length = window.history.length;

        await render();

        expect(lastTable().view.page).toBe(10);
        expect(window.location.search).toBe('?page=give-payment-history&give-paged=10');
        expect(window.history.length).toBe(length);
    });

    it('never reads or writes the address in content mode', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-payment-history&give-paged=3&donor=12`);
        const href = window.location.href;

        await render({contentMode: true});
        await changeView((view) => ({...view, page: 2}));

        expect(firstView().page).toBe(1);
        expect(window.location.href).toBe(href);
    });

    it('stamps the first entry with its own tab state, so Back never borrows the search of a later entry', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-payment-history`);

        await render();

        expect(window.history.state?.giveListTable).toEqual({screen: 'donations', search: ''});
    });

    it('never writes the address without a screen config, and reads only the filters it names', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-payment-history&give-paged=3&donor=58`);
        const href = window.location.href;

        await render({screen: undefined});

        expect(firstView().page).toBe(1);
        expect(Object.fromEntries(listRequests()[0].searchParams)).toMatchObject({donor: '58', page: '1'});

        await changeView((view) => ({...view, page: 2}));

        expect(window.location.href).toBe(href);
    });

    it('does not ask for the list again when only the columns or density change, even with a Period filter', async () => {
        jest.useFakeTimers({
            now: new Date('2026-10-28T10:00:00Z'),
            doNotFake: ['setTimeout', 'setInterval', 'clearTimeout', 'clearInterval', 'setImmediate', 'clearImmediate'],
        });
        window.history.replaceState(null, '', `${PATH}?page=give-payment-history&give-period=7`);

        try {
            await render({
                filterSettings: [
                    ...filterSettings,
                    {
                        name: 'periodFilter',
                        type: 'filterby',
                        groupedOptions: [
                            {
                                id: 'period',
                                apiParam: 'start',
                                name: 'Period',
                                type: 'radio',
                                options: [{text: 'Last 7 days', value: '7'}],
                                toApiValue: (days: string, now: Date) =>
                                    new Date(now.getTime() - Number(days) * 86_400_000).toISOString(),
                            },
                        ],
                    },
                ],
            });
            const before = listRequests();

            expect(before[0].searchParams.get('start')).toBe('2026-10-21T10:00:00.000Z');

            jest.setSystemTime(new Date('2026-10-28T10:05:00Z'));
            await changeView((view) => ({...view, layout: {density: 'comfortable'}}));
            await changeView((view) => ({...view, fields: ['amount']}));

            expect(listRequests()).toHaveLength(before.length);
        } finally {
            jest.useRealTimers();
        }
    });
});
