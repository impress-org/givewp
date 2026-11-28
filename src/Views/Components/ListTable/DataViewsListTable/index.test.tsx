import {readFileSync} from 'fs';
import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {Field, View} from '@wordpress/dataviews';
import type {ListTableItem} from '../ListTablePage/types';
import DataViewsListTable from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

jest.mock('./styles.scss', () => ({}));
// The installed `Button` does not know `__next40pxDefaultSize` and React warns; the one WordPress ships does.
jest.mock('@wordpress/components', () => {
    const {createElement, forwardRef} = jest.requireActual('react');
    const actual = jest.requireActual('@wordpress/components');
    const Button = forwardRef(({__next40pxDefaultSize, ...props}: Record<string, unknown>, ref: unknown) =>
        createElement(actual.Button, {...props, ref})
    );
    return {...actual, Button};
});

const fields: Field<ListTableItem>[] = [{id: 'name', label: 'Name', getValue: ({item}) => String(item.name ?? '')}];
const items = Array.from({length: 30}, (_, index) => ({id: index + 1, name: `Row ${index + 1}`}));
const view = (page: number): View => ({type: 'table', page, perPage: 30, fields: ['name']});

let container: HTMLDivElement;
let root: Root;

const render = (props: {page: number; rows: ListTableItem[]; totalItems: number | null; hasNextPage?: boolean; layout?: object; isLoading?: boolean; hasError?: boolean; viewExtras?: object; search?: string}) =>
    act(async () => {
        root.render(
            <DataViewsListTable
                view={{...view(props.page), search: props.search, ...props.viewExtras}}
                onChangeView={() => undefined}
                fields={fields}
                actions={[]}
                data={props.rows}
                paginationInfo={
                    props.totalItems === null
                        ? null
                        : {totalItems: props.totalItems, totalPages: Math.ceil(props.totalItems / 30)}
                }
                hasNextPage={props.hasNextPage}
                isLoading={props.isLoading ?? false}
                hasError={props.hasError}
                selection={[]}
                onChangeSelection={() => undefined}
                empty={null}
                perPageSizes={[30]}
                search={false}
                {...props.layout}
            />
        );
    });

beforeEach(() => {
    // jsdom has no ResizeObserver, which the View options popover uses.
    (globalThis as {ResizeObserver?: unknown}).ResizeObserver = class {
        observe() {}
        unobserve() {}
        disconnect() {}
    };
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(() => {
    act(() => root.unmount());
    container.remove();
    delete (globalThis as {ResizeObserver?: unknown}).ResizeObserver;
});

describe('DataViewsListTable total', () => {
    it('shows the rows on the page out of every row that matches, as the next DataViews does', async () => {
        await render({page: 1, rows: items, totalItems: 462});

        expect(container.querySelector('.givewp-list-table__total')?.textContent).toBe('30 of 462 Items');
    });

    it('counts only the rows on the last page', async () => {
        await render({page: 3, rows: items.slice(0, 12), totalItems: 72});

        expect(container.querySelector('.givewp-list-table__total')?.textContent).toBe('12 of 72 Items');
    });

    it('shows only the rows when they are all the rows that match', async () => {
        await render({page: 1, rows: items.slice(0, 12), totalItems: 12});

        expect(container.querySelector('.givewp-list-table__total')?.textContent).toBe('12 Items');
    });

    it('writes the numbers in the browser language', async () => {
        await render({page: 1, rows: items, totalItems: 1200});
        expect(container.querySelector('.givewp-list-table__total')?.textContent).toBe('30 of 1,200 Items');

        jest.spyOn(window.navigator, 'language', 'get').mockReturnValue('de-DE');
        await render({page: 1, rows: items, totalItems: 1200});
        expect(container.querySelector('.givewp-list-table__total')?.textContent).toBe('30 of 1.200 Items');

        await render({page: 1, rows: items.slice(0, 1), totalItems: 1});
        expect(container.querySelector('.givewp-list-table__total')?.textContent).toBe('1 Item');

        jest.restoreAllMocks();
    });

    it('keeps the strings of core, so the existing translations still match', () => {
        const source = readFileSync('src/Views/Components/ListTable/dataviewsPolyfill/ListTableTotal.tsx', 'utf8');

        expect(source).toContain("_n('%1$d of %2$d Item', '%1$d of %2$d Items', totalItems)");
        expect(source).toContain("_n('%d Item', '%d Items', rows)");
    });

    it('is still needed: the stock footer of the pinned DataViews prints the rows on the page, with no total', () => {
        // When this fails the `@wordpress/dataviews` pin moved. Read `ListTableFooter`: the stock footer may now print
        // the total itself, and it must still write it in the browser language.
        const stock = readFileSync('node_modules/@wordpress/dataviews/build-module/components/dataviews-bulk-actions/index.js', 'utf8');

        expect(stock).toContain('_n("%d Item", "%d Items", data.length)');
        expect(stock).not.toContain('of %2$d Item');
    });

    it('shows nothing when no row matches', async () => {
        await render({page: 1, rows: [], totalItems: 0});

        expect(container.querySelector('.givewp-list-table__total')).toBeNull();
    });
});

describe('DataViewsListTable page loading', () => {
    beforeEach(() => jest.useFakeTimers());
    afterEach(() => jest.useRealTimers());

    const visibleLoading = () => container.querySelector('.givewp-list-table__footer .givewp-list-table__loading');
    const statusText = () => container.querySelector('.give-visually-hidden[role="status"]')?.textContent;

    it('shows nothing for a fast page', async () => {
        await render({page: 2, rows: items, totalItems: 462, isLoading: true});
        await act(() => jest.advanceTimersByTime(299));

        expect(visibleLoading()).toBeNull();
        expect(statusText()).toBe('');

        await render({page: 2, rows: items, totalItems: 462, isLoading: false});
        await act(() => jest.advanceTimersByTime(2000));

        expect(visibleLoading()).toBeNull();
        expect(statusText()).toBe('');
    });

    it('shows the footer text after 300 ms, hidden from screen readers', async () => {
        await render({page: 2, rows: items, totalItems: 462, isLoading: true});
        await act(() => jest.advanceTimersByTime(300));

        expect(visibleLoading()?.textContent).toBe('Loading…');
        expect(visibleLoading()?.getAttribute('aria-hidden')).toBe('true');
        expect(container.querySelector('.givewp-list-table__progress')).toBeNull();
        expect(statusText()).toBe('');
    });

    // A list opened on page 2 or later has no rows and no total yet, and DataViews shows its own spinner
    // where the rows will be.
    it('leaves the one visible spinner to DataViews while there are no rows, and still announces', async () => {
        await render({page: 2, rows: [], totalItems: null, isLoading: true});
        await act(() => jest.advanceTimersByTime(1000));

        expect(visibleLoading()).toBeNull();
        expect(statusText()).toBe('Loading…');
        expect(container.querySelector('.givewp-list-table__footer')).not.toBeNull();
    });

    it('gives screen readers the words after 1 s, and removes everything when the page arrives', async () => {
        await render({page: 2, rows: items, totalItems: 462, isLoading: true});
        await act(() => jest.advanceTimersByTime(1000));

        expect(statusText()).toBe('Loading…');

        await render({page: 2, rows: items, totalItems: 462, isLoading: false});

        expect(visibleLoading()).toBeNull();
        expect(statusText()).toBe('');
    });

    it('keeps the old rows on screen while the next page loads, marked busy and on the loading class', async () => {
        await render({page: 2, rows: items, totalItems: 462, isLoading: true});

        expect(container.querySelector('.dataviews-view-table')?.getAttribute('aria-busy')).toBe('true');
        expect(container.querySelector('.givewp-list-table')?.classList.contains('givewp-list-table--loading')).toBe(true);
        expect(container.querySelectorAll('tbody tr').length).toBe(30);
    });
});

describe('DataViewsListTable selection while a page loads', () => {
    const rowCheckbox = () => container.querySelector<HTMLInputElement>('tbody input[type="checkbox"]');
    const actions = [{id: 'x', label: 'X', supportsBulk: true, callback: () => undefined}];

    const renderWithSelection = (isLoading: boolean, onChangeSelection: (ids: string[]) => void) =>
        act(async () => {
            root.render(
                <DataViewsListTable
                    view={view(1)}
                    onChangeView={() => undefined}
                    fields={fields}
                    actions={actions}
                    data={items}
                    paginationInfo={{totalItems: 462, totalPages: 16}}
                    isLoading={isLoading}
                    selection={[]}
                    onChangeSelection={onChangeSelection}
                    empty={null}
                    perPageSizes={[30]}
                />
            );
        });

    it('passes a selection change on while the rows are not loading', async () => {
        const onChange = jest.fn();
        await renderWithSelection(false, onChange);
        await act(() => rowCheckbox()?.click());

        expect(onChange).toHaveBeenCalled();
    });

    it('ignores a selection change while the next page loads', async () => {
        const onChange = jest.fn();
        await renderWithSelection(true, onChange);
        await act(() => rowCheckbox()?.click());

        expect(onChange).not.toHaveBeenCalled();
    });
});

describe('DataViewsListTable after a failed request', () => {
    it('keeps the page control, with the last known pages, so the user can go back', async () => {
        await render({page: 3, rows: items, totalItems: 462});
        await render({page: 3, rows: [], totalItems: 0, hasError: true});

        expect(container.querySelector('.dataviews-pagination')).not.toBeNull();
        expect(container.textContent).toContain('of 16');
        expect(container.querySelector('.givewp-list-table__total')).toBeNull();
    });

    it('shows no page control when the first request fails', async () => {
        await render({page: 1, rows: [], totalItems: 0, hasError: true});

        expect(container.querySelector('.dataviews-pagination')).toBeNull();
    });
});

describe('DataViewsListTable after a failed request for other filters', () => {
    it('does not show the pages of the previous search', async () => {
        await render({page: 3, rows: items, totalItems: 462, search: 'smith'});
        await render({page: 1, rows: [], totalItems: null, hasError: true, search: 'jones'});

        expect(container.textContent).not.toContain('of 16');
    });
});

describe('DataViewsListTable page control switch', () => {
    const pageInput = () => container.querySelector('.givewp-list-table__page-input');

    it('uses the stock control at 100 pages and the number box at 101', async () => {
        await render({page: 1, rows: items, totalItems: 3000});

        expect(pageInput()).toBeNull();
        expect(container.querySelector('.dataviews-pagination')).not.toBeNull();

        await render({page: 1, rows: items, totalItems: 3030});

        expect(pageInput()).not.toBeNull();
    });

    it('keeps the stock control above 100 pages when infinite scroll is on', async () => {
        await render({page: 1, rows: items, totalItems: 3030, viewExtras: {infiniteScrollEnabled: true}});

        expect(pageInput()).toBeNull();
    });
});

describe('DataViewsListTable view controls', () => {
    // "Reset view" is not in the toolbar: it is added inside the View options popover.
    it('orders the view options, then the more options menu', async () => {
        await render({
            page: 1,
            rows: items,
            totalItems: 30,
            layout: {
                isDefaultLayout: false,
                onResetLayout: () => undefined,
                viewMenu: <button aria-label="More view options" />,
            },
        });

        const labels = Array.from(container.querySelectorAll('.givewp-list-table__view-controls button')).map(
            (button) => button.getAttribute('aria-label') ?? button.textContent
        );

        expect(labels).toEqual(['View options', 'More view options']);
    });

    // Fails when a DataViews bump changes the popover markup that `ListTableViewReset` relies on.
    it('shows Reset view in the heading of the real View options popover', async () => {
        const onResetLayout = jest.fn();
        await render({page: 1, rows: items, totalItems: 30, layout: {isDefaultLayout: false, onResetLayout}});
        await act(async () => container.querySelector<HTMLElement>('button[aria-label="View options"]')?.click());
        await act(async () => new Promise((resolve) => setTimeout(resolve, 50)));

        const reset = document.querySelector<HTMLButtonElement>(
            '.dataviews-config__popover .dataviews-settings-section__sidebar > .givewp-view-config-reset > button'
        );
        expect(reset?.textContent).toBe('Reset view');
        expect(reset?.parentElement?.parentElement?.querySelector('h2')?.textContent).toBe('Appearance');

        await act(async () => reset?.click());
        expect(onResetLayout).toHaveBeenCalledTimes(1);
    });
});

describe('DataViewsListTable column widths', () => {
    // When this fails after a DataViews bump, the column classes changed. Rename the selectors of our width
    // rules in `dataviewsPolyfill/_polyfill.scss` (`col-primary` becomes `col-first-data`) and update this test. See "Column
    // widths" in docs/architecture/list-tables.md.
    it('keeps the column classes that our width rules use', async () => {
        await render({
            page: 1,
            rows: items,
            totalItems: 30,
            viewExtras: {titleField: 'name', fields: []},
            layout: {actions: [{id: 'delete', label: 'Delete', supportsBulk: true, callback: () => undefined}]},
        });

        expect(container.querySelector('colgroup col.dataviews-view-table__col-primary')).not.toBeNull();
        expect(container.querySelector('colgroup col.dataviews-view-table__col-checkbox')).not.toBeNull();
    });
});

describe('DataViewsListTable while the total is not known', () => {
    const arrows = () => [...container.querySelectorAll<HTMLButtonElement>('.givewp-list-table__footer button')];

    it('shows only the previous and next arrows, with no page box, no "of N" and no total', async () => {
        await render({page: 1, rows: items, totalItems: null});

        expect(arrows().map((button) => button.getAttribute('aria-label'))).toEqual(['Previous page', 'Next page']);
        expect(container.querySelector('.givewp-list-table__page-input, select')).toBeNull();
        expect(container.querySelector('.givewp-list-table__footer')?.textContent).not.toContain(' of ');
        expect(container.querySelector('.givewp-list-table__total')).toBeNull();
    });

    it('turns Previous off on page 1 and on above it', async () => {
        await render({page: 1, rows: items, totalItems: null});

        expect(arrows()[0].getAttribute('aria-disabled')).toBe('true');

        await render({page: 4, rows: items, totalItems: null});

        expect(arrows()[0].getAttribute('aria-disabled')).toBe('false');
    });

    it('follows hasNextPage for Next, and treats it as on by default', async () => {
        await render({page: 1, rows: items, totalItems: null});

        expect(arrows()[1].getAttribute('aria-disabled')).toBe('false');

        await render({page: 1, rows: items, totalItems: null, hasNextPage: false});

        expect(arrows()[1].getAttribute('aria-disabled')).toBe('true');
    });

    it('goes to the next and previous page', async () => {
        const onChangeView = jest.fn();
        await act(async () => {
            root.render(
                <DataViewsListTable
                    view={view(2)}
                    onChangeView={onChangeView}
                    fields={fields}
                    actions={[]}
                    data={items}
                    paginationInfo={null}
                    isLoading={false}
                    selection={[]}
                    onChangeSelection={() => undefined}
                    empty={null}
                    perPageSizes={[30]}
                />
            );
        });

        await act(() => arrows()[1].click());
        await act(() => arrows()[0].click());

        expect(onChangeView.mock.calls.map(([next]) => next.page)).toEqual([3, 1]);
    });

    it('shows no footer for an empty first page, and Previous for an empty page past it', async () => {
        await render({page: 1, rows: [], totalItems: null});

        expect(container.querySelector('.givewp-list-table__footer')).toBeNull();

        await render({page: 5, rows: [], totalItems: null});

        expect(arrows()[0].getAttribute('aria-disabled')).toBe('false');
    });

    it('shows the normal control and the total when the count arrives', async () => {
        await render({page: 1, rows: items, totalItems: null});
        await render({page: 1, rows: items, totalItems: 462});

        expect(container.querySelector('.givewp-list-table__total')?.textContent).toBe('30 of 462 Items');
        expect(container.querySelector('.dataviews-pagination')?.textContent).toContain('of 16');
    });

    it('keeps the dim and the loading text working', async () => {
        jest.useFakeTimers();
        await render({page: 2, rows: items, totalItems: null, isLoading: true});
        await act(() => jest.advanceTimersByTime(300));

        expect(container.querySelector('.givewp-list-table--loading')).not.toBeNull();
        expect(container.querySelector('.givewp-list-table__footer .givewp-list-table__loading')).not.toBeNull();
        jest.useRealTimers();
    });

    it('keeps Previous after a failed request on a later page', async () => {
        await render({page: 3, rows: [], totalItems: null, hasError: true});

        expect(arrows()[0].getAttribute('aria-disabled')).toBe('false');
    });
});
