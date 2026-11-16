import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {Field, View} from '@wordpress/dataviews';
import type {ListTableItem} from '../ListTablePage/types';
import DataViewsListTable from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

jest.mock('./styles.scss', () => ({}));

const fields: Field<ListTableItem>[] = [{id: 'name', label: 'Name', getValue: ({item}) => String(item.name ?? '')}];
const items = Array.from({length: 30}, (_, index) => ({id: index + 1, name: `Row ${index + 1}`}));
const view = (page: number): View => ({type: 'table', page, perPage: 30, fields: ['name']});

let container: HTMLDivElement;
let root: Root;

const render = (props: {page: number; rows: ListTableItem[]; totalItems: number; layout?: object; isLoading?: boolean}) =>
    act(async () => {
        root.render(
            <DataViewsListTable
                view={view(props.page)}
                onChangeView={() => undefined}
                fields={fields}
                actions={[]}
                data={props.rows}
                paginationInfo={{totalItems: props.totalItems, totalPages: Math.ceil(props.totalItems / 30)}}
                isLoading={props.isLoading ?? false}
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
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(() => {
    act(() => root.unmount());
    container.remove();
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

    it('shows nothing when no row matches', async () => {
        await render({page: 1, rows: [], totalItems: 0});

        expect(container.querySelector('.givewp-list-table__total')).toBeNull();
    });
});

describe('DataViewsListTable page loading', () => {
    it('says a page is loading next to the page control, and is silent otherwise', async () => {
        await render({page: 2, rows: items, totalItems: 462, isLoading: true});

        const status = container.querySelector('.givewp-list-table__footer .givewp-list-table__loading');
        expect(status?.getAttribute('role')).toBe('status');
        expect(status?.textContent).toBe('Loading…');

        await render({page: 2, rows: items, totalItems: 462, isLoading: false});

        expect(container.querySelector('.givewp-list-table__loading')?.textContent).toBe('');
    });

    it('keeps the old rows on screen while the next page loads, marked busy', async () => {
        await render({page: 2, rows: items, totalItems: 462, isLoading: true});

        expect(container.querySelector('.dataviews-view-table')?.getAttribute('aria-busy')).toBe('true');
        expect(container.querySelectorAll('tbody tr').length).toBe(30);
    });
});

describe('DataViewsListTable view controls', () => {
    // Reset view comes and goes, so it sits before the controls that must not move.
    it('orders Reset view, then the view options, then the more options menu', async () => {
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

        expect(labels).toEqual(['Reset view', 'View options', 'More view options']);
    });
});
