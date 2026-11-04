import {act, isValidElement, type ReactElement, type ReactNode} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import apiFetch from '@wordpress/api-fetch';
import {SWRConfig} from 'swr';
import {filterFieldId, TEST_MODE_FILTER} from '../adapters/viewToParameters';
import type {DataViewsListTableProps} from '../DataViewsListTable';
import type {FilterDeclaration} from '../filters/types';
import type {ListTableFiltersProps} from '../ListTableFilters';
import type {ListTablePageHeaderProps} from './ListTablePageHeader';
import type {FilterConfig, ListTablePageProps} from './types';
import ListTablePage from './index';

jest.mock('@wordpress/api-fetch', () => jest.fn());
jest.mock('@givewp/components/AdminDetailsPage/Notifications', () => () => null);
jest.mock('../filters/siteDate', () => ({
    ...jest.requireActual('../filters/siteDate'),
    siteToday: () => '2026-10-20',
}));

const mockRenderedHeader = jest.fn<null, [ListTablePageHeaderProps]>(() => null);
const lastHeader = () => mockRenderedHeader.mock.calls[mockRenderedHeader.mock.calls.length - 1][0];
jest.mock('./ListTablePageHeader', () => (props: ListTablePageHeaderProps) => mockRenderedHeader(props));

const mockRenderedTable = jest.fn<null, [DataViewsListTableProps]>(() => null);
jest.mock('../DataViewsListTable', () => (props: DataViewsListTableProps) => mockRenderedTable(props));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockedFetch = apiFetch as unknown as jest.Mock;
const API_ROOT = 'https://example.test/wp-json/give-api/v2/admin/forms';
const PATH = '/wp-admin/edit.php';

const search: FilterDeclaration = {
    kind: 'search',
    id: 'search',
    label: 'Search forms',
    hint: 'Searches form title or form ID.',
    parameter: 'search',
};
const status: FilterDeclaration = {
    kind: 'options',
    id: 'status',
    label: 'Status',
    isPrimary: true,
    multiple: true,
    options: 'statuses',
    parameter: 'status',
    exclusiveValues: ['trash'],
};
const campaign: FilterDeclaration = {
    kind: 'entity',
    id: 'campaign',
    label: 'Campaign',
    isPrimary: true,
    source: 'campaign',
    multiple: true,
    parameter: 'campaignId',
    allowNone: true,
};
const date: FilterDeclaration = {
    kind: 'date',
    id: 'date',
    label: 'Date created',
    parameters: {start: 'start', end: 'end'},
};

const props = (overrides: Partial<ListTablePageProps> = {}): ListTablePageProps => ({
    title: 'Donation Forms',
    apiSettings: {
        apiRoot: API_ROOT,
        apiNonce: 'nonce',
        table: {
            columns: [
                {id: 'id', label: 'ID', sortable: true},
                {id: 'title', label: 'Title', sortable: true},
            ],
            statuses: [
                {value: 'publish', label: 'Published', filterable: true},
                {value: 'pending', label: 'Pending', filterable: true},
                {value: 'trash', label: 'Trash', filterable: true},
            ],
        },
    },
    filters: [search, campaign, status, date],
    listTableBlankSlate: null,
    screen: {key: 'donation-forms'},
    ...overrides,
});

let root: Root;
let container: HTMLDivElement;
let slotRoot: Root | undefined;
let slotContainer: HTMLDivElement | undefined;

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

/**
 * Renders a node the page handed to the table (the search box or the filter bar) on its own.
 */
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
        .filter((url) => url.pathname.endsWith('/forms'));
const lastRequest = () => Object.fromEntries(listRequests()[listRequests().length - 1].searchParams);
const lastTable = (): DataViewsListTableProps =>
    mockRenderedTable.mock.calls[mockRenderedTable.mock.calls.length - 1][0];
const filterBar = () => {
    const bar = lastTable().filterBar;

    if (!isValidElement(bar)) {
        throw new Error('No filter bar');
    }

    return bar as ReactElement<ListTableFiltersProps>;
};

beforeEach(() => {
    mockedFetch.mockReset();
    mockedFetch.mockResolvedValue({items: [], totalItems: 0, totalPages: 0});
    mockRenderedTable.mockClear();
    mockRenderedHeader.mockClear();
    // The search term is copied to session storage; a term from an earlier test must not leak in.
    window.sessionStorage.clear();
    window.history.replaceState(null, '', `${PATH}?page=give-forms`);
});

afterEach(async () => {
    await act(() => root.unmount());

    if (slotRoot) {
        await act(() => slotRoot?.unmount());
        slotContainer?.remove();
        slotRoot = undefined;
    }
});

describe('ListTablePage with filter declarations', () => {
    it('with filters, the first request carries URL filter values as endpoint parameters', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-forms&give-status=publish,pending&give-campaign=12`);

        await render();

        expect(listRequests()).toHaveLength(1);
        expect(lastRequest()).toMatchObject({status: 'publish,pending', campaignId: '12'});
        expect(lastRequest()).not.toHaveProperty('give-status');
    });

    it('a date preset is sent as whole-day bounds of the site date', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-forms&give-date=7`);

        await render();

        expect(lastRequest()).toMatchObject({start: '2026-10-14T00:00:00', end: '2026-10-20T23:59:59'});
    });

    it('scope is sent with every request and never shown as a chip', async () => {
        await render({filters: [search, status, date], scope: {campaignId: 7}, contentMode: true});

        expect(lastRequest()).toMatchObject({campaignId: '7'});

        await act(async () =>
            filterBar().props.onChangeView({
                ...lastTable().view,
                filters: [{field: 'filter-status', operator: 'isAny', value: ['pending']}],
            })
        );

        expect(lastRequest()).toMatchObject({campaignId: '7', status: 'pending'});

        const bar = await renderSlot(lastTable().filterBar);

        expect(bar.textContent).not.toContain('7');
        expect(filterBar().props.view.filters?.map((filter) => filter.field)).toEqual(['filter-status']);
    });

    it('reset keeps the scope', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-forms&give-status=pending`);
        await render({filters: [search, status, date], scope: {campaignId: 7}});

        expect(lastRequest()).toMatchObject({campaignId: '7', status: 'pending'});
        expect(filterBar().props.canReset).toBe(true);

        await act(async () => filterBar().props.onReset());

        expect(lastTable().view.filters).toEqual([]);
        expect(lastRequest()).toMatchObject({campaignId: '7'});
        expect(lastRequest()).not.toHaveProperty('status');
    });

    it('reset moves keyboard focus to the search box, as the Reset button goes away', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-forms&give-status=pending`);
        await render();
        const box = await renderSlot(lastTable().searchBox);

        await act(async () => filterBar().props.onReset());

        expect(document.activeElement).toBe(box.querySelector('input'));
    });

    it('reset moves keyboard focus to a control left in the bar when the search box is hidden', async () => {
        // During a bulk selection the table shows no search box, but the filter bar stays.
        window.history.replaceState(null, '', `${PATH}?page=give-forms&give-status=pending`);
        await render();
        const bar = await renderSlot(lastTable().filterBar);

        await act(async () => filterBar().props.onReset());

        expect(document.activeElement).toBe(bar.querySelector('button'));
        expect(document.activeElement?.textContent).not.toBe('Reset');
    });

    it('reset focuses the bar itself when the search box is hidden and no control is left', async () => {
        await render();
        const bar = await renderSlot(lastTable().filterBar);
        bar.querySelectorAll('button').forEach((button) => button.remove());

        await act(async () => filterBar().props.onReset());

        expect(document.activeElement).toBe(bar.querySelector('[role="group"]'));
        expect(document.activeElement?.getAttribute('aria-label')).toBe('Filters');
    });

    it('can reset a secondary filter added from "Add filter" that has no value yet', async () => {
        await render();
        await act(async () =>
            filterBar().props.onChangeView({
                ...lastTable().view,
                filters: [{field: 'filter-date', operator: 'is', value: undefined}],
            })
        );

        expect(filterBar().props.canReset).toBe(true);
        expect(lastRequest()).not.toHaveProperty('start');
    });

    it('does not announce the result count of the page load, also with filters in the URL', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-forms&give-status=pending&give-date=7&s=spring`);
        mockedFetch.mockResolvedValue({items: [], totalItems: 3, totalPages: 1});
        await render({testModeToggle: {label: 'Test mode', ariaLabel: 'Show test mode'}, paymentMode: true});
        await act(() => new Promise((resolve) => setTimeout(resolve, 400)));

        expect(container.querySelector('[role="status"]')?.textContent).toBe('');
    });

    it('does not announce the result count again when a filter is added with no value yet', async () => {
        mockedFetch.mockResolvedValue({items: [], totalItems: 3, totalPages: 1});
        await render();
        const status = () => container.querySelector('[role="status"]')?.textContent;
        await act(async () =>
            filterBar().props.onChangeView({
                ...lastTable().view,
                filters: [{field: 'filter-status', operator: 'isAny', value: ['pending']}],
            })
        );
        await act(() => new Promise((resolve) => setTimeout(resolve, 400)));

        expect(status()).toBe('3 items found');

        await act(async () =>
            filterBar().props.onChangeView({
                ...lastTable().view,
                filters: [
                    {field: 'filter-status', operator: 'isAny', value: ['pending']},
                    {field: 'filter-date', operator: 'is', value: undefined},
                ],
            })
        );
        await act(() => new Promise((resolve) => setTimeout(resolve, 400)));

        // A repeat would end with a no-break space so screen readers read it again.
        expect(status()).toBe('3 items found');
    });

    it('filter-only fields are not passed to DataViews', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-forms&give-status=pending`);

        await render();

        expect(lastTable().fields.map((field) => field.id)).toEqual(['id', 'title']);
    });

    it("the search box shows the declaration's label and hint", async () => {
        await render();

        const box = await renderSlot(lastTable().searchBox);
        const input = box.querySelector('input')!;
        const label = box.querySelector(`label[for="${input.id}"]`);
        const hint = document.getElementById(input.getAttribute('aria-describedby') ?? '');

        expect(label?.textContent).toBe('Search forms');
        expect(hint?.textContent).toBe('Searches form title or form ID.');
    });

    it('sends the search term from the search box', async () => {
        jest.useFakeTimers();
        await render();
        const box = await renderSlot(lastTable().searchBox);
        const input = box.querySelector('input')!;

        await act(async () => {
            const setValue = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')?.set;
            setValue?.call(input, 'spring');
            input.dispatchEvent(new Event('input', {bubbles: true}));
        });
        await act(async () => jest.runOnlyPendingTimers());
        jest.useRealTimers();
        await act(async () => {});

        expect(lastTable().view.search).toBe('spring');
        expect(lastRequest()).toMatchObject({search: 'spring'});
    });

    it('a rejected request shows the error state and keeps the chips', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-forms&give-status=publish,pending`);
        mockedFetch.mockRejectedValue({code: 'rest_invalid_param', data: {status: 400}});

        await render();

        const empty = await renderSlot(lastTable().empty);

        expect(empty.textContent).toContain('There was a problem retrieving');

        await act(() => slotRoot?.unmount());
        slotRoot = undefined;
        const bar = await renderSlot(lastTable().filterBar);

        expect(bar.textContent).toContain('Status: Published, Pending');
    });

    it('turns test mode on by default on a test site and shows the switch', async () => {
        await render({testModeToggle: {label: 'Test mode', ariaLabel: 'Show test mode'}, paymentMode: true});

        expect(lastRequest()).toMatchObject({testMode: 'true'});
        expect(mockRenderedHeader.mock.calls[0][0].testModeToggle).toEqual({
            label: 'Test mode',
            ariaLabel: 'Show test mode',
        });
        expect(lastHeader().testMode).toBe(true);
    });

    describe('Reset shows only when the filters differ from the screen defaults', () => {
        const testModeProps = {
            testModeToggle: {label: 'Test mode', ariaLabel: 'Show test mode'},
            paymentMode: true,
            listTableBlankSlate: <p>No forms found</p>,
        };

        it('hides Reset on an untouched view whose default holds test mode', async () => {
            await render(testModeProps);

            expect(lastHeader().testMode).toBe(true);
            expect(filterBar().props.canReset).toBe(false);
        });

        it('shows the blank slate, not the filtered empty state, on an untouched view', async () => {
            await render(testModeProps);

            const empty = await renderSlot(lastTable().empty);

            expect(empty.textContent).toBe('No forms found');
        });

        it('shows Reset after a filter is added', async () => {
            await render(testModeProps);
            await act(async () =>
                filterBar().props.onChangeView({
                    ...lastTable().view,
                    filters: [
                        ...(lastTable().view.filters ?? []),
                        {field: 'filter-status', operator: 'isAny', value: ['pending']},
                    ],
                })
            );

            expect(filterBar().props.canReset).toBe(true);
        });

        it('shows Reset when test mode is turned off on a test site', async () => {
            await render(testModeProps);
            await act(async () => lastHeader().onChangeTestMode(false));

            expect(filterBar().props.canReset).toBe(true);
        });

        it('Reset goes back to the defaults, keeps test mode on and hides Reset', async () => {
            window.history.replaceState(null, '', `${PATH}?page=give-forms&give-status=pending`);
            await render(testModeProps);

            expect(filterBar().props.canReset).toBe(true);

            await act(async () => filterBar().props.onReset());

            expect(lastTable().view.filters).toEqual([
                {field: filterFieldId(TEST_MODE_FILTER), operator: 'is', value: true},
            ]);
            expect(filterBar().props.canReset).toBe(false);
            expect(lastHeader().testMode).toBe(true);
            expect(lastRequest()).toMatchObject({testMode: 'true'});
            expect(lastRequest()).not.toHaveProperty('status');
        });

        it('a search term is a change from the defaults', async () => {
            await render(testModeProps);
            await act(async () => lastTable().onChangeView({...lastTable().view, search: 'spring'}));

            expect(filterBar().props.canReset).toBe(true);
        });
    });

    it('a screen with neither filters nor filter settings shows no search box and an empty bar', async () => {
        await render({filters: undefined});

        expect(lastTable().searchBox).toBeUndefined();
        expect(filterBar().props.declarations).toEqual([]);
    });
});

describe('ListTablePage with only the deprecated filter settings (an add-on screen)', () => {
    const filterSettings: FilterConfig[] = [
        {name: 'campaignId', type: 'campaignselect', options: [{value: 'none', text: 'No campaign'}]},
        {name: 'search', type: 'search', text: 'Search by name or ID'},
        {
            name: 'state',
            type: 'select',
            text: 'State',
            options: [
                {value: 'any', text: 'All'},
                {value: 'active', text: 'Active'},
            ],
        },
        {name: 'toggle', type: 'checkbox', text: 'Test Mode', ariaLabel: 'View test donations'},
    ];
    const addOnProps = {filters: undefined, filterSettings};

    it('renders the filter bar and the labeled search box from the mapped declarations', async () => {
        await render(addOnProps);

        expect(filterBar().props.declarations.map((declaration) => declaration.id)).toEqual(['campaignId', 'state']);

        const box = await renderSlot(lastTable().searchBox);
        const input = box.querySelector('input')!;

        expect(box.querySelector(`label[for="${input.id}"]`)?.textContent).toBe('Search by name or ID');
    });

    it('sends an explicit scope with every request, also after Clear filters', async () => {
        await render({
            filters: undefined,
            filterSettings: [{name: 'search', type: 'search', text: 'Search'}],
            scope: {campaignId: 7},
        });

        expect(lastRequest()).toMatchObject({campaignId: '7'});

        await act(async () => lastTable().onChangeView({...lastTable().view, search: 'spring'}));

        expect(lastRequest()).toMatchObject({campaignId: '7', search: 'spring'});

        const empty = await renderSlot(lastTable().empty);
        const clear = Array.from(empty.querySelectorAll('button')).find(
            (button) => button.textContent === 'Clear filters'
        );
        await act(async () => clear?.click());

        expect(lastTable().view.search).toBe('');
        expect(listRequests().length).toBeGreaterThan(1);
        expect(listRequests().every((url) => url.searchParams.get('campaignId') === '7')).toBe(true);
    });

    it('lets an explicit scope win over the hidden filter settings of a tab', async () => {
        await render({
            filters: undefined,
            contentMode: true,
            filterSettings: [
                {name: 'campaignId', type: 'hidden', options: [{text: '', value: '3'}]},
                {name: 'donorId', type: 'hidden', options: [{text: '', value: '9'}]},
            ],
            scope: {campaignId: 7},
        });

        expect(lastRequest()).toMatchObject({campaignId: '7', donorId: '9'});
    });

    it('gives the header its test-mode switch from the toggle setting', async () => {
        await render(addOnProps);

        expect(mockRenderedHeader.mock.calls[0][0].testModeToggle).toEqual({
            label: 'Test Mode',
            ariaLabel: 'View test donations',
        });
    });

    it('sends the old parameters: the select default, a linked campaign, a chosen value and the search', async () => {
        window.history.replaceState(null, '', `${PATH}?page=give-forms&campaignId=4`);

        await render(addOnProps);

        expect(lastRequest()).toMatchObject({state: 'any', campaignId: '4'});

        await act(async () =>
            lastTable().onChangeView({
                ...lastTable().view,
                search: 'spring',
                filters: [
                    ...(lastTable().view.filters ?? []),
                    {field: filterFieldId('state'), operator: 'is', value: 'active'},
                ],
            })
        );

        expect(lastRequest()).toMatchObject({state: 'active', campaignId: '4', search: 'spring'});
    });

    it('sends a hidden filter of a list in a tab as fixed scope, never as a chip', async () => {
        await render({
            filters: undefined,
            contentMode: true,
            filterSettings: [{name: 'campaignId', type: 'hidden', options: [{value: '7', text: 'All Campaign Forms'}]}],
        });

        expect(lastRequest()).toMatchObject({campaignId: '7'});
        expect(filterBar().props.declarations).toEqual([]);
    });

    it('applies a standalone hidden filter by default, and Reset keeps it', async () => {
        await render({
            filters: undefined,
            filterSettings: [
                {name: 'donor', type: 'hidden', text: 'Donor', options: [{value: '12', text: 'Donor 12'}]},
            ],
        });

        expect(lastRequest()).toMatchObject({donor: '12'});
        expect(filterBar().props.canReset).toBe(false);
    });
});
