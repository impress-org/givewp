import type {ReactNode} from 'react';
import {__experimentalHStack as HStack} from '@wordpress/components';
import {DataViews} from '@wordpress/dataviews';
import type {Action, Field, View} from '@wordpress/dataviews';
import type {ListTableItem} from '../ListTablePage/types';
import ListTableViewReset from '../ListTableViewReset';
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
    /** True while the layout is the screen default. Hides "Reset view". */
    isDefaultLayout?: boolean;
    /** Puts the layout back to the screen defaults. Without it there is no "Reset view". */
    onResetLayout?: () => void;
}

const getItemId = (item: ListTableItem) => String(item.id);

/**
 * The stock DataViews layout, with two changes: while rows are selected, the bulk actions also take
 * the place of the search row above the table, so they show where the selection starts (the stock
 * footer keeps its own copy next to the pagination); and a "Reset view" button sits after the view
 * options, which this DataViews version does not offer.
 *
 * @since TBD
 */
function ListTableLayout({
    hasSelection,
    search,
    searchLabel,
    isDefaultLayout,
    onResetLayout,
}: {
    hasSelection: boolean;
    search: boolean;
    searchLabel?: string;
    isDefaultLayout: boolean;
    onResetLayout?: () => void;
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
                        {search && <DataViews.Search label={searchLabel} />}
                        <DataViews.FiltersToggle />
                    </HStack>
                    <HStack spacing={1} expanded={false} style={{flexShrink: 0}}>
                        <DataViews.LayoutSwitcher />
                        <DataViews.ViewConfig />
                        {onResetLayout && <ListTableViewReset isDefault={isDefaultLayout} onConfirm={onResetLayout} />}
                    </HStack>
                </HStack>
            )}
            <DataViews.FiltersToggled className="dataviews-filters__container" />
            <DataViews.Layout />
            <DataViews.Footer />
        </>
    );
}

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
    isDefaultLayout = true,
    onResetLayout,
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
            >
                <ListTableLayout
                    hasSelection={selection.length > 0}
                    search={search}
                    searchLabel={searchLabel}
                    isDefaultLayout={isDefaultLayout}
                    onResetLayout={onResetLayout}
                />
            </DataViews>
        </div>
    );
}
