import {act, type ReactNode} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import apiFetch from '@wordpress/api-fetch';
import {SWRConfig} from 'swr';
import type {DataViewsListTableProps} from '../DataViewsListTable';
import type {FilterDeclaration} from '../filters/types';
import type {ListTablePageProps, StatConfig} from './types';
import ListTablePage from './index';

jest.mock('@wordpress/api-fetch', () => jest.fn());
jest.mock('@givewp/components/AdminDetailsPage/Notifications', () => () => null);

const mockRenderedTable = jest.fn<null, [DataViewsListTableProps]>(() => null);
jest.mock('../DataViewsListTable', () => (props: DataViewsListTableProps) => mockRenderedTable(props));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockedFetch = apiFetch as unknown as jest.Mock;
const API_ROOT = 'https://example.test/wp-json/give-api/v2/admin/donations';
const PATH = '/wp-admin/edit.php';

const search: FilterDeclaration = {kind: 'search', id: 'search', label: 'Search', hint: '', parameter: 'search'};
const donationType: FilterDeclaration = {
    kind: 'options',
    id: 'donationType',
    label: 'Donation type',
    multiple: false,
    options: [
        {value: 'one-time', label: 'One-time'},
        {value: 'recurring', label: 'Recurring'},
    ],
    parameter: 'donationType',
};

const statsConfig: Record<string, StatConfig> = {
    donationsCount: {label: 'Total Donations', filter: {filterId: 'donationType'}},
    oneTimeDonationsCount: {label: 'One-Time Donations', filter: {filterId: 'donationType', value: 'one-time'}},
    recurringDonationsCount: {
        label: 'Recurring Donations',
        filter: {filterId: 'donationType', value: 'recurring'},
    },
};

let root: Root;
let container: HTMLDivElement;
let slotRoot: Root | undefined;
let slotContainer: HTMLDivElement | undefined;

const render = (overrides: Partial<ListTablePageProps> = {}) => {
    const props: ListTablePageProps = {
        title: 'Donations',
        apiSettings: {apiRoot: API_ROOT, apiNonce: 'nonce', table: {columns: [{id: 'id', label: 'ID', sortable: true}]}},
        filters: [search, donationType],
        statsConfig,
        listTableBlankSlate: null,
        screen: {id: 'donations', defaults: {}} as unknown as ListTablePageProps['screen'],
        ...overrides,
    };

    return act(async () =>
        root.render(
            <SWRConfig value={{provider: () => new Map(), dedupingInterval: 0}}>
                <ListTablePage {...props} />
            </SWRConfig>
        )
    );
};

async function renderSlot(node: ReactNode) {
    slotContainer = document.createElement('div');
    document.body.appendChild(slotContainer);
    slotRoot = createRoot(slotContainer);
    await act(async () => slotRoot?.render(<>{node}</>));

    return slotContainer;
}

const listRequests = () =>
    mockedFetch.mock.calls
        .filter(([options]) => options.url)
        .map(([options]) => new URL(options.url))
        .filter((url) => url.pathname.endsWith('/donations'));
const lastRequest = () => Object.fromEntries(listRequests()[listRequests().length - 1].searchParams);
const lastTable = (): DataViewsListTableProps =>
    mockRenderedTable.mock.calls[mockRenderedTable.mock.calls.length - 1][0];
const tile = (label: string) =>
    Array.from(container.querySelectorAll('.givewp-stat-tile')).find((element) =>
        element.textContent?.includes(label)
    ) as HTMLElement;
const pressed = () =>
    Array.from(container.querySelectorAll('button.givewp-stat-tile--pressed')).map((button) => button.textContent);

beforeEach(() => {
    mockedFetch.mockReset();
    mockedFetch.mockImplementation(({url}: {url: string}) =>
        Promise.resolve(
            url.includes('/stats')
                ? {
                      donationsCount: 9,
                      oneTimeDonationsCount: 6,
                      recurringDonationsCount: 3,
                      formatted: {donationsCount: '9', oneTimeDonationsCount: '6', recurringDonationsCount: '3'},
                  }
                : {items: [], totalItems: 0, totalPages: 0}
        )
    );
    mockRenderedTable.mockClear();
    window.sessionStorage.clear();
    window.history.replaceState(null, '', `${PATH}?page=give-donations`);
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();

    if (slotRoot) {
        await act(() => slotRoot?.unmount());
        slotContainer?.remove();
        slotRoot = undefined;
    }
});

describe('ListTablePage stat tiles that filter', () => {
    it('Total is pressed while no filter is set', async () => {
        await render();

        expect(pressed()).toEqual(['Total Donations9']);
    });

    it('clicking a tile sends the filter parameter, shows the chip and presses only that tile', async () => {
        await render({});
        await act(async () => lastTable().onChangeView({...lastTable().view, page: 3}));
        await act(async () => tile('One-Time Donations').click());

        expect(lastRequest()).toMatchObject({donationType: 'one-time', page: '1'});
        expect(pressed()).toEqual(['One-Time Donations6']);

        const bar = await renderSlot(lastTable().filterBar);

        expect(bar.textContent).toContain('Donation type');
    });

    it('the pressed tile follows a value set from the toolbar, and a removed filter un-presses it', async () => {
        await render();
        await act(async () => tile('One-Time Donations').click());
        await act(async () =>
            lastTable().onChangeView({
                ...lastTable().view,
                filters: [{field: 'filter-donationType', operator: 'is', value: 'recurring'}],
            })
        );

        expect(lastRequest()).toMatchObject({donationType: 'recurring'});
        expect(pressed()).toEqual(['Recurring Donations3']);

        await act(async () => lastTable().onChangeView({...lastTable().view, filters: []}));

        expect(lastTable().view.filters).toEqual([]);
        // The request for "no filter" is already cached, so no new request is sent for it.
        expect(pressed()).toEqual(['Total Donations9']);
    });

    it('clicking the pressed tile, or Total, clears the filter', async () => {
        await render();
        await act(async () => tile('Recurring Donations').click());
        await act(async () => tile('Recurring Donations').click());

        expect(lastTable().view.filters).toEqual([]);

        await act(async () => tile('Recurring Donations').click());

        expect(lastTable().view.filters).toHaveLength(1);

        await act(async () => tile('Total Donations').click());

        expect(lastTable().view.filters).toEqual([]);
        expect(pressed()).toEqual(['Total Donations9']);
    });

    it('with the filter not declared, no tile is a button', async () => {
        await render({filters: [search]});

        expect(container.querySelectorAll('.givewp-stat-tile').length).toBe(3);
        expect(container.querySelectorAll('button.givewp-stat-tile')).toHaveLength(0);
    });

    it('tiles with no filter mapping stay plain statistics', async () => {
        await render({statsConfig: {donationsCount: {label: 'Total Donations'}}});

        expect(container.querySelectorAll('button.givewp-stat-tile')).toHaveLength(0);
    });
});
