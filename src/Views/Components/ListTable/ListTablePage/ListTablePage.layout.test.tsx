import {act} from 'react';
import {select} from '@wordpress/data';
import {createRoot, type Root} from 'react-dom/client';
import type {View} from '@wordpress/dataviews';
import type {DataViewsListTableProps} from '../DataViewsListTable';
import type {ListTablePageProps, ListTableResponse} from './types';
import ListTablePage from './index';

jest.mock('@givewp/components/AdminDetailsPage/Notifications', () => () => null);
jest.mock('../filters/siteDate', () => ({
    ...jest.requireActual('../filters/siteDate'),
    siteToday: () => '2026-11-02',
}));

let mockData: ListTableResponse = {items: [], totalItems: 0, totalPages: 1};
jest.mock('../hooks/useListTableQuery', () => ({
    __esModule: true,
    default: () => ({
        data: mockData,
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

let mockNarrow = false;
jest.mock('../hooks/useNarrowLayout', () => ({__esModule: true, default: () => mockNarrow}));

// The real table renders, and its props are kept so a test can act as DataViews would.
const mockTableProps = jest.fn<void, [DataViewsListTableProps]>();
jest.mock('../DataViewsListTable', () => {
    const {default: Actual} = jest.requireActual('../DataViewsListTable');

    return {
        __esModule: true,
        default: (props: DataViewsListTableProps) => {
            mockTableProps(props);

            return <Actual {...props} />;
        },
    };
});

const mockEnqueueLayoutWrite = jest.fn();
jest.mock('../state/layoutWriteQueue', () => ({
    ...jest.requireActual('../state/layoutWriteQueue'),
    enqueueLayoutWrite: (...args: unknown[]) => mockEnqueueLayoutWrite(...args),
}));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

// jsdom has no ResizeObserver, which the DataViews layouts use.
class ResizeObserverStub {
    observe() {}
    unobserve() {}
    disconnect() {}
}
(globalThis as {ResizeObserver?: unknown}).ResizeObserver ??= ResizeObserverStub;

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

const cardProps: ListTablePageProps = {
    title: 'Donations',
    apiSettings: {
        apiRoot: 'https://example.test/wp-json/give-api/v2/admin/donations',
        apiNonce: 'nonce',
        table: {
            columns: [
                {id: 'id', label: 'ID', sortable: true, kind: 'text'},
                {id: 'donor', label: 'Donor', sortable: false, kind: 'text'},
                {id: 'amount', label: 'Amount', sortable: true, kind: 'money'},
                {id: 'status', label: 'Status', sortable: false, kind: 'status'},
                {id: 'form', label: 'Form', sortable: false, kind: 'text'},
            ],
        },
    },
    filters: [
        {kind: 'search', id: 'search', label: 'Search donations', hint: '', parameter: 'search'},
        {
            kind: 'options',
            id: 'status',
            label: 'Status',
            isPrimary: true,
            multiple: true,
            options: [{value: 'failed', label: 'Failed'}],
            parameter: 'status',
        },
    ],
    rowActions: [{id: 'delete', label: 'Delete', supportsBulk: true, run: () => Promise.resolve()}],
    listTableBlankSlate: null,
    contentMode: true,
    screen: {
        key: 'donations',
        fieldRoles: {primary: 'donor', description: 'amount', secondary: ['status']},
    },
};

let container: HTMLDivElement;
let root: Root;

const lastTable = (): DataViewsListTableProps => mockTableProps.mock.calls[mockTableProps.mock.calls.length - 1][0];

async function setNarrow(isNarrow: boolean, pageProps: ListTablePageProps = cardProps) {
    mockNarrow = isNarrow;
    await act(() => root.render(<ListTablePage {...pageProps} />));
}

const follows = (first: Element | null, second: Element | null) =>
    Boolean(first && second && first.compareDocumentPosition(second) & Node.DOCUMENT_POSITION_FOLLOWING);

beforeEach(() => {
    mockNarrow = false;
    mockData = {items: [], totalItems: 0, totalPages: 1};
    mockTableProps.mockClear();
    mockEnqueueLayoutWrite.mockClear();
    window.GiveListTableLayouts = {path: '/givewp/v3/list-table-layouts', layouts: {}};
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
    jest.useRealTimers();
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
            container.querySelector('.givewp-list-table'),
        ];

        regions.forEach((region) => expect(region).not.toBeNull());
        regions.slice(1).forEach((region, index) => {
            expect(follows(regions[index], region)).toBe(true);
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

        expect(follows(title, button)).toBe(true);
    });
});

describe('ListTablePage narrow layout', () => {
    beforeEach(() => {
        mockData = {
            items: [
                {id: 1, donor: 'Ada Lovelace', amount: '$10.00', status: 'Complete', form: 'Main form'},
                {id: 2, donor: 'Alan Turing', amount: '$20.00', status: 'Pending', form: 'Main form'},
            ],
            totalItems: 200,
            totalPages: 10,
        };
    });

    it('shows cards and no table at 782 px and below', async () => {
        await setNarrow(true);

        expect(container.querySelector('.dataviews-view-grid')).not.toBeNull();
        expect(container.querySelector('.dataviews-view-table')).toBeNull();
    });

    it('shows the table above 782 px', async () => {
        await setNarrow(false);

        expect(container.querySelector('.dataviews-view-table')).not.toBeNull();
        expect(container.querySelector('.dataviews-view-grid')).toBeNull();
    });

    it('offers no layout switcher', async () => {
        await setNarrow(true);

        expect(container.querySelector('button[aria-label="Layout"]')).toBeNull();
    });

    it('shows the primary field as the card title and the ID as a badge', async () => {
        await setNarrow(true);

        const card = container.querySelector('.dataviews-view-grid__card') as HTMLElement;

        expect(card.querySelector('.dataviews-view-grid__title-field')?.textContent).toContain('Ada Lovelace');
        expect(card.querySelector('.dataviews-view-grid__badge-fields')?.textContent).toContain('1');
        expect(card.querySelector('.dataviews-view-grid__fields')?.textContent).toContain('Complete');
        // Not one of the roles, so hidden on the card.
        expect(card.textContent).not.toContain('Main form');
    });

    it('lets each card be selected', async () => {
        await setNarrow(true);

        const checkbox = container.querySelector<HTMLInputElement>(
            '.dataviews-view-grid__card .givewp-list-table__card-checkbox input'
        );

        expect(checkbox?.getAttribute('aria-label')).toBe('Ada Lovelace');

        await act(async () => checkbox?.click());

        expect(lastTable().selection).toEqual(['1']);
    });

    it('resizing keeps page, sort, filters and selection', async () => {
        jest.useFakeTimers();
        await setNarrow(false);

        const filters = [{field: 'status', operator: 'isAny' as const, value: ['failed']}];
        const sort = {field: 'amount', direction: 'asc' as const};
        await act(async () => lastTable().onChangeView({...lastTable().view, filters, sort}));
        await act(async () => lastTable().onChangeView({...lastTable().view, page: 3}));
        await act(async () => lastTable().onChangeSelection(['2']));
        const wideFields = lastTable().view.fields;

        await setNarrow(true);

        expect(lastTable().view).toMatchObject({type: 'grid', page: 3, sort, filters});
        expect(lastTable().selection).toEqual(['2']);

        await setNarrow(false);

        expect(lastTable().view).toMatchObject({type: 'table', page: 3, sort, filters, fields: wideFields});
        expect(lastTable().selection).toEqual(['2']);

        // Resizing is not a layout change, so nothing is stored, and never the card layout.
        await act(async () => jest.advanceTimersByTime(5000));
        expect(mockEnqueueLayoutWrite).not.toHaveBeenCalled();
    });

    it('maps a change made on the cards back onto the table view', async () => {
        await setNarrow(false);
        const wideView = lastTable().view;

        await setNarrow(true);
        await act(async () => lastTable().onChangeView({...lastTable().view, page: 4}));

        expect(lastTable().view).toMatchObject({type: 'grid', page: 4});

        await setNarrow(false);

        expect(lastTable().view).toEqual({...wideView, page: 4});
    });

    it('orders the narrow toolbar search, filters, then view options', async () => {
        await setNarrow(true);

        const search = container.querySelector('.dataviews__search');
        const filterBar = container.querySelector('.givewp-list-table-filters');
        const viewOptions = container.querySelector('.givewp-list-table__view-controls');

        expect(follows(search, filterBar)).toBe(true);
        expect(follows(filterBar, viewOptions)).toBe(true);
        // On its own row, under the filters.
        expect(viewOptions?.closest('.givewp-list-table__filter-row')).toBeNull();
    });
});

describe('ListTablePage legacy view menu', () => {
    const menuButton = () => container.querySelector<HTMLButtonElement>('button[aria-label="More view options"]');
    const legacyItem = () =>
        Array.from(document.querySelectorAll<HTMLElement>('[role="menuitem"]')).find(
            (item) => item.textContent?.trim() === 'Switch to Legacy View'
        );

    it('offers no view menu without legacyView', async () => {
        await setNarrow(false, props);

        expect(menuButton()).toBeNull();
    });

    it.each([false, true])('puts the menu after the view options (narrow: %s)', async (isNarrow) => {
        await setNarrow(isNarrow, {...cardProps, legacyView: {}});

        const viewOptions = container.querySelector('button[aria-label="View options"]');

        expect(menuButton()).not.toBeNull();
        expect(menuButton()?.closest('.givewp-list-table__view-controls')).not.toBeNull();
        expect(follows(viewOptions, menuButton())).toBe(true);
    });

    it('never puts "Switch to Legacy View" in the header', async () => {
        await setNarrow(false, {...props, legacyView: {}});

        expect(container.querySelector('header')?.textContent).not.toContain('Switch to Legacy View');
    });

    it('saves the legacy view choice through the list endpoint, then calls onSwitched', async () => {
        const onSwitched = jest.fn();
        const fetchMock = jest.fn(() => Promise.resolve({ok: true, json: () => Promise.resolve({})}));
        const originalFetch = globalThis.fetch;
        globalThis.fetch = fetchMock as unknown as typeof fetch;

        try {
            await setNarrow(false, {
                ...props,
                legacyView: {ariaLabel: 'Switch to the legacy donations table view', onSwitched},
            });
            await act(async () => menuButton()?.click());

            expect(legacyItem()?.getAttribute('aria-label')).toBe('Switch to the legacy donations table view');

            await act(async () => legacyItem()?.click());

            const url = new URL((fetchMock.mock.calls[0] as unknown as [string])[0]);
            expect(url.pathname).toBe('/wp-json/give-api/v2/admin/donations/view');
            expect(url.searchParams.get('isLegacy')).toBe('1');
            expect(onSwitched).toHaveBeenCalledTimes(1);
        } finally {
            globalThis.fetch = originalFetch;
        }
    });

    it('shows an error notice and does not switch when the save fails', async () => {
        const onSwitched = jest.fn();
        const fetchMock = jest.fn(() => Promise.resolve({ok: false, status: 500, json: () => Promise.resolve({})}));
        const originalFetch = globalThis.fetch;
        globalThis.fetch = fetchMock as unknown as typeof fetch;

        try {
            await setNarrow(false, {...props, legacyView: {onSwitched}});
            await act(async () => menuButton()?.click());
            await act(async () => legacyItem()?.click());

            expect(onSwitched).not.toHaveBeenCalled();
            const notices = select('givewp/admin-details-page-notifications').getNotifications() as {content: string}[];

            expect(notices.map((notice) => notice.content)).toContain(
                'The legacy view could not be saved. Please try again.'
            );
        } finally {
            globalThis.fetch = originalFetch;
        }
    });
});
