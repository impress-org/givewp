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

const render = (props: {page: number; rows: ListTableItem[]; totalItems: number}) =>
    act(async () => {
        root.render(
            <DataViewsListTable
                view={view(props.page)}
                onChangeView={() => undefined}
                fields={fields}
                actions={[]}
                data={props.rows}
                paginationInfo={{totalItems: props.totalItems, totalPages: Math.ceil(props.totalItems / 30)}}
                isLoading={false}
                selection={[]}
                onChangeSelection={() => undefined}
                empty={null}
                perPageSizes={[30]}
                search={false}
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
