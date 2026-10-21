import type {MouseEvent} from 'react';
import type {Field} from '@wordpress/dataviews';
import InterweaveSSR from '../InterweaveSSR';
import type {Column, ColumnFilterConfig, ListTableItem, ListTableResponse} from '../ListTablePage/types';

/**
 * DataViews toggles row selection when a row is clicked. A link or button inside a
 * server-rendered cell must do its own job without also selecting the row.
 *
 * @since TBD
 */
export function keepInteractiveClicksInCell(event: MouseEvent<HTMLElement>) {
    if (!(event.target as HTMLElement).closest('a, button')) {
        return;
    }

    event.stopPropagation();
}

/**
 * Plain text of a server-rendered cell, for places that need text rather than markup (for
 * example the row checkbox label DataViews builds from the title field).
 *
 * @since TBD
 */
export function cellText(value: unknown): string {
    if (typeof value !== 'string') {
        return value === null || value === undefined ? '' : String(value);
    }

    return new DOMParser().parseFromString(value, 'text/html').body.textContent?.trim() ?? '';
}

/**
 * Maps the server column schema (`ModelColumn::toArray()`) to DataViews fields, in server order.
 * Cells stay server-rendered HTML through InterweaveSSR, unless the screen passes a column filter
 * that renders the column itself.
 *
 * @since TBD
 */
export function columnsToFields(
    columns: Column[],
    columnFilters: ColumnFilterConfig[] = [],
    data?: ListTableResponse
): Field<ListTableItem>[] {
    return columns.map((column) => {
        const columnFilter = columnFilters.find((definition) => definition.column === column.id);

        return {
            id: column.id,
            label: column.label,
            enableSorting: column.sortable,
            filterBy: false,
            getValue: ({item}) => cellText(item[column.id]),
            render: ({item}) => (
                // eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions
                <div onClick={keepInteractiveClicksInCell}>
                    {columnFilter ? columnFilter.filter(item, column, data) : <InterweaveSSR column={column} item={item} />}
                </div>
            ),
        };
    });
}

/**
 * Ids of the columns shown by default, in server order. The title field is left out because
 * DataViews shows it in its own leading column.
 *
 * @since TBD
 */
export function visibleFieldIds(columns: Column[], titleField?: string): string[] {
    return columns
        .filter((column) => column.visible !== false && column.id !== titleField)
        .map((column) => column.id);
}

/**
 * The column that names a row. DataViews shows it first and uses it to label the row checkbox.
 * Same order of preference as the old table's checkbox labels.
 *
 * @since TBD
 */
export function titleFieldId(columns: Column[]): string | undefined {
    return ['donor', 'title', 'donorInformation'].find((id) => columns.some((column) => column.id === id));
}
