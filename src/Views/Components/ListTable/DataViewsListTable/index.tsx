import type {ReactNode} from 'react';
import {DataViews} from '@wordpress/dataviews';
import type {Action, Field, View} from '@wordpress/dataviews';
import type {ListTableItem} from '../ListTablePage/types';
import './styles.scss';

/**
 * @since TBD
 */
export interface DataViewsListTableProps {
    view: View;
    onChangeView: (view: View) => void;
    fields: Field<ListTableItem>[];
    actions: Action<ListTableItem>[];
    data: ListTableItem[];
    paginationInfo: {totalItems: number; totalPages: number};
    isLoading: boolean;
    selection: string[];
    onChangeSelection: (ids: string[]) => void;
    empty: ReactNode;
    perPageSizes: number[];
    search: boolean;
    searchLabel?: string;
}

const getItemId = (item: ListTableItem) => String(item.id);

/**
 * Renders the stock DataViews table for an admin list screen. No data fetching and no
 * Give-specific behavior inside the DataViews area.
 *
 * @since TBD
 */
export default function DataViewsListTable({
    view,
    onChangeView,
    fields,
    actions,
    data,
    paginationInfo,
    isLoading,
    selection,
    onChangeSelection,
    empty,
    perPageSizes,
    search,
    searchLabel,
}: DataViewsListTableProps) {
    return (
        <div className="givewp-list-table">
            <DataViews<ListTableItem>
                view={view}
                onChangeView={onChangeView}
                fields={fields}
                actions={actions}
                data={data}
                getItemId={getItemId}
                paginationInfo={paginationInfo}
                isLoading={isLoading}
                selection={selection}
                onChangeSelection={onChangeSelection}
                empty={empty}
                config={{perPageSizes}}
                search={search}
                searchLabel={searchLabel}
                defaultLayouts={{table: {}}}
            />
        </div>
    );
}
