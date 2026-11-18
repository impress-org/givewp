import type {ReactNode} from 'react';
import {__experimentalHStack as HStack} from '@wordpress/components';
import {DataViews} from '@wordpress/dataviews';
import {_n, sprintf} from '@wordpress/i18n';
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
 * Whether any row on the page can be part of a bulk action. The stock footer decides its checkbox the
 * same way.
 */
const hasBulkActions = (actions: Action<ListTableItem>[], data: ListTableItem[]) =>
    data.some((item) =>
        actions.some((action) => action.supportsBulk && (!action.isEligible || action.isEligible(item)))
    );

/**
 * The footer message of the next DataViews pin (12.0.1, WordPress 7.0), without the selection case: "30
 * of 464 Items" when more rows match than are on the page, else "30 Items". It uses the stock count's
 * class and the same strings as DataViews (core's text domain), so it looks and reads the same.
 */
function ListTableTotal({rows, totalItems}: {rows: number; totalItems: number}) {
    const message =
        totalItems > rows
            ? sprintf(
                  /* translators: %1$d: number of items. %2$d: total number of items. */
                  _n('%1$d of %2$d Item', '%1$d of %2$d Items', totalItems),
                  rows,
                  totalItems
              )
            : sprintf(
                  /* translators: %d: number of items. */
                  _n('%d Item', '%d Items', rows),
                  rows
              );

    return <span className="givewp-list-table__total dataviews-bulk-actions-footer__item-count">{message}</span>;
}

/**
 * The stock DataViews footer with one change. The stock footer counts only the rows on the page ("30
 * Items"), which reads as the total, so this one shows the total next to the selection checkbox
 * instead. While rows are selected, the stock count of the selection ("2 Items selected") shows.
 *
 * Remove this footer (and `ListTableTotal`, `hasBulkActions` and the `__total` rules in styles.scss)
 * when the `@wordpress/dataviews` pin moves to 12.0.1 or later (WordPress 7.0). From that version the
 * stock footer prints the total ("30 of 464 Items") from `paginationInfo.totalItems`, which this table
 * already passes. To go back to the stock footer, put `<DataViews.Footer />` in `ListTableLayout`. It
 * was added in one commit, "Enhancement: show the total row count in the list table footer" (find it
 * with `git log --grep`); reverting it removes all of this.
 */
function ListTableFooter({
    actions,
    data,
    totalItems,
    totalPages,
    hasSelection,
}: {
    actions: Action<ListTableItem>[];
    data: ListTableItem[];
    totalItems: number;
    totalPages: number;
    hasSelection: boolean;
}) {
    if (totalItems <= 0 || totalPages <= 0 || data.length === 0) {
        return null;
    }

    return (
        <HStack expanded={false} justify="end" className="dataviews-footer givewp-list-table__footer">
            {hasBulkActions(actions, data) && <DataViews.BulkActionToolbar />}
            {!hasSelection && <ListTableTotal rows={data.length} totalItems={totalItems} />}
            <DataViews.Pagination />
        </HStack>
    );
}

/**
 * The stock DataViews layout, with three changes: the footer shows the total row count; while rows are selected, the bulk actions also take
 * the place of the search row above the table, so they show where the selection starts (the stock
 * footer keeps its own copy next to the pagination); and a "Reset view" button sits after the view
 * options, which this DataViews version does not offer.
 *
 * @since TBD
 */
function ListTableLayout({
    actions,
    data,
    totalItems,
    totalPages,
    hasSelection,
    search,
    searchLabel,
    isDefaultLayout,
    onResetLayout,
}: {
    actions: Action<ListTableItem>[];
    data: ListTableItem[];
    totalItems: number;
    totalPages: number;
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
            <ListTableFooter
                actions={actions}
                data={data}
                totalItems={totalItems}
                totalPages={totalPages}
                hasSelection={hasSelection}
            />
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
        <div className={`givewp-list-table${selection.length > 0 ? ' givewp-list-table--has-selection' : ''}`}>
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
                    actions={actions}
                    data={data}
                    totalItems={paginationInfo.totalItems}
                    totalPages={paginationInfo.totalPages}
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
