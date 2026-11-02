import type {ReactNode} from 'react';
import {__experimentalHStack as HStack} from '@wordpress/components';
import {DataViews} from '@wordpress/dataviews';
import type {Action, Field, View} from '@wordpress/dataviews';
import type {ListTableItem} from '../ListTablePage/types';
import ListTableViewReset from '../ListTableViewReset';
import './styles.scss';

/**
 * @since TBD Drop `search` and `searchLabel`: the search box and the filter bar are always Give's.
 * @since TBD Add the `searchBox` and `filterBar` slots.
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
    /** True while the layout is the screen default. Hides "Reset view". */
    isDefaultLayout?: boolean;
    /** Puts the layout back to the screen defaults. Without it there is no "Reset view". */
    onResetLayout?: () => void;
    /** Give's labeled search box, in place of the DataViews one. None when the list has no search. */
    searchBox?: ReactNode;
    /** Give's filter bar, in place of the DataViews filter toggle and filter row. */
    filterBar?: ReactNode;
}

const getItemId = (item: ListTableItem) => String(item.id);

/**
 * The stock DataViews layout, with two changes: while rows are selected, the bulk actions also take
 * the place of the search row above the table, so they show where the selection starts (the stock
 * footer keeps its own copy next to the pagination); and a "Reset view" button sits after the view
 * options, which this DataViews version does not offer. Give's search box and filter bar always take
 * the place of the stock search and filter row.
 *
 * @since TBD Always render the `searchBox` and `filterBar` slots; no stock search or filter row.
 * @since TBD Render the `searchBox` and `filterBar` slots in place of the stock search and filters.
 * @since TBD
 */
function ListTableLayout({
    hasSelection,
    isDefaultLayout,
    onResetLayout,
    searchBox,
    filterBar,
}: {
    hasSelection: boolean;
    isDefaultLayout: boolean;
    onResetLayout?: () => void;
    searchBox?: ReactNode;
    filterBar?: ReactNode;
}) {
    return (
        <>
            {hasSelection ? (
                <HStack justify="start" className="dataviews__view-actions givewp-list-table__bulk-actions">
                    <DataViews.BulkActionToolbar />
                </HStack>
            ) : (
                <HStack alignment="top" justify="space-between" className="dataviews__view-actions" spacing={1}>
                    <HStack justify="start" expanded={false} className="dataviews__search">
                        {searchBox}
                    </HStack>
                    <HStack spacing={1} expanded={false} style={{flexShrink: 0}}>
                        <DataViews.LayoutSwitcher />
                        <DataViews.ViewConfig />
                        {onResetLayout && <ListTableViewReset isDefault={isDefaultLayout} onConfirm={onResetLayout} />}
                    </HStack>
                </HStack>
            )}
            {filterBar}
            <DataViews.Layout />
            <DataViews.Footer />
        </>
    );
}

/**
 * Renders the stock DataViews table for an admin list screen. No data fetching and no
 * Give-specific behavior inside the DataViews area.
 *
 * @since TBD Drop `search` and `searchLabel`; DataViews' own search is always off.
 * @since TBD Accept the `searchBox` and `filterBar` slots.
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
    isDefaultLayout = true,
    onResetLayout,
    searchBox,
    filterBar,
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
                search={false}
                defaultLayouts={{table: {}}}
            >
                <ListTableLayout
                    hasSelection={selection.length > 0}
                    isDefaultLayout={isDefaultLayout}
                    onResetLayout={onResetLayout}
                    searchBox={searchBox}
                    filterBar={filterBar}
                />
            </DataViews>
        </div>
    );
}
