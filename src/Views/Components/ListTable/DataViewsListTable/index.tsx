import {
    type ComponentProps,
    createContext,
    createElement,
    type MutableRefObject,
    type ReactNode,
    useContext,
    useMemo,
    useRef,
} from 'react';
import cx from 'classnames';
import {CheckboxControl, __experimentalHStack as HStack} from '@wordpress/components';
import {DataViews} from '@wordpress/dataviews';
import {__, _n, sprintf} from '@wordpress/i18n';
import type {Action, Field, View} from '@wordpress/dataviews';
import type {ListTableItem} from '../ListTablePage/types';
import ListTableViewReset from '../ListTableViewReset';
import useHorizontalOverflow from '../hooks/useHorizontalOverflow';
import './styles.scss';

/**
 * How the list is laid out: a table, or one card per record at 782 px and below.
 *
 * @since TBD
 */
export type ListTableLayoutMode = 'wide' | 'narrow';

/**
 * @since TBD Add the `viewMenu` slot.
 * @since TBD Add `layoutMode`.
 * @since TBD Drop `search` and `searchLabel`: the search box and the filter bar are always Give's.
 * @since TBD Add the `searchBox`, `searchHint` and `filterBar` slots.
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
    /** The search box's hint line, under the search and the filters. Hidden with the search box. */
    searchHint?: ReactNode;
    /** Give's filter bar, in place of the DataViews filter toggle and filter row. */
    filterBar?: ReactNode;
    /** `narrow` shows the cards (the view must be a grid view). Default `wide`. */
    layoutMode?: ListTableLayoutMode;
    /** Give's "More view options" menu, right after the view options and "Reset view", in both layouts. */
    viewMenu?: ReactNode;
}

const getItemId = (item: ListTableItem) => String(item.id);

const canSelect = (actions: Action<ListTableItem>[], item: ListTableItem) =>
    actions.some((action) => action.supportsBulk && (!action.isEligible || action.isEligible(item)));

/**
 * What the card titles read: the selection and the actions. They come through context, not through
 * the field's `render` function: DataViews draws the title field as a component, so a new `render`
 * function on every selection change would remount the card, and the checkbox would lose keyboard focus.
 */
const CardSelectionContext = createContext<{
    selection: string[];
    onChangeSelection: (ids: string[]) => void;
    actions: Action<ListTableItem>[];
}>({selection: [], onChangeSelection: () => {}, actions: []});

type CardTitleProps = {
    field: Field<ListTableItem>;
    renderProps: ComponentProps<NonNullable<Field<ListTableItem>['render']>>;
};

/** The card title with a selection checkbox before it, when the record can be selected. */
function CardTitle({field, renderProps}: CardTitleProps) {
    const {selection, onChangeSelection, actions} = useContext(CardSelectionContext);
    const {item} = renderProps;
    const id = getItemId(item);
    const label = String(field.getValue?.({item}) ?? '');
    const title = field.render ? createElement(field.render, renderProps) : label;

    if (!canSelect(actions, item)) {
        return <>{title}</>;
    }

    return (
        <HStack justify="start" alignment="center" spacing={2} className="givewp-list-table__card-title">
            <CheckboxControl
                className="givewp-list-table__card-checkbox"
                __nextHasNoMarginBottom
                /* translators: %s: the name of the record, for example a donor name. */
                aria-label={sprintf(__('Select %s', 'give'), label)}
                checked={selection.includes(id)}
                onChange={() =>
                    onChangeSelection(
                        selection.includes(id) ? selection.filter((selected) => selected !== id) : [...selection, id]
                    )
                }
            />
            {title}
        </HStack>
    );
}

/**
 * Puts a selection checkbox before the card title. The cards of this DataViews version show their
 * checkbox only over a media image, and the list has no media, so without this a card could only be
 * selected with Ctrl or Cmd and a click.
 *
 * The returned `render` functions only change when the fields change, never with the selection or the
 * actions (see CardSelectionContext).
 */
function withCardCheckbox(fields: Field<ListTableItem>[], titleField: string | undefined): Field<ListTableItem>[] {
    return fields.map((field) =>
        field.id !== titleField
            ? field
            : {...field, render: (renderProps) => <CardTitle field={field} renderProps={renderProps} />}
    );
}

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
 * The stock DataViews layout, with these changes: the footer shows the total row count; while rows are
 * selected, the bulk actions also take the place of the search row above the table, so they show where
 * the selection starts (the stock footer keeps its own copy next to the pagination); and a "Reset view"
 * button sits after the view options, which this DataViews version does not offer. Give's search box and filter bar always take
 * the place of the stock search and filter row.
 *
 * @since TBD Render the `viewMenu` slot after "Reset view", in both layouts.
 * @since TBD In the narrow layout, put the view options on their own row under the filters, with no layout switcher.
 * @since TBD Always render the `searchBox` and `filterBar` slots; no stock search or filter row.
 * @since TBD Show the view options on the filter bar's row instead of next to the search box.
 * @since TBD Render the `searchBox` and `filterBar` slots in place of the stock search and filters.
 * @since TBD
 */
function ListTableLayout({
    actions,
    data,
    totalItems,
    totalPages,
    hasSelection,
    isDefaultLayout,
    onResetLayout,
    searchBox,
    searchHint,
    filterBar,
    layoutMode,
    viewMenu,
}: {
    actions: Action<ListTableItem>[];
    data: ListTableItem[];
    totalItems: number;
    totalPages: number;
    hasSelection: boolean;
    isDefaultLayout: boolean;
    onResetLayout?: () => void;
    searchBox?: ReactNode;
    searchHint?: ReactNode;
    filterBar?: ReactNode;
    layoutMode: ListTableLayoutMode;
    viewMenu?: ReactNode;
}) {
    const viewControls = !hasSelection && (
        <HStack spacing={1} expanded={false} className="givewp-list-table__view-controls">
            <DataViews.ViewConfig />
            {onResetLayout && <ListTableViewReset isDefault={isDefaultLayout} onConfirm={onResetLayout} />}
            {viewMenu}
        </HStack>
    );
    const isNarrow = layoutMode === 'narrow';

    return (
        <>
            {hasSelection ? (
                <HStack justify="start" className="dataviews__view-actions givewp-list-table__bulk-actions">
                    <DataViews.BulkActionToolbar />
                </HStack>
            ) : (
                <HStack alignment="top" justify="start" className="dataviews__view-actions" spacing={1}>
                    <HStack justify="start" className="dataviews__search">
                        {searchBox}
                    </HStack>
                </HStack>
            )}
            <div className="givewp-list-table__filter-row">
                {filterBar}
                {!isNarrow && viewControls}
            </div>
            {!hasSelection && searchHint}
            {isNarrow && viewControls && <div className="givewp-list-table__view-controls-row">{viewControls}</div>}
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
 * @since TBD Add the `viewMenu` slot.
 * @since TBD Add `layoutMode`: the cards at 782 px and below, the table above.
 * @since TBD Add the `--more-start` and `--more-end` modifiers while the list hides content at that edge.
 * @since TBD Drop `search` and `searchLabel`; DataViews' own search is always off.
 * @since TBD Accept the `searchBox`, `searchHint` and `filterBar` slots.
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
    searchHint,
    filterBar,
    layoutMode = 'wide',
    viewMenu,
}: DataViewsListTableProps) {
    const rootRef = useRef<HTMLDivElement>(null);
    const {hasMoreStart, hasMoreEnd} = useHorizontalOverflow(rootRef);
    const isNarrow = layoutMode === 'narrow';
    const hasBulkActions = actions.some((action) => action.supportsBulk);
    const shownFields = useMemo(
        () => (isNarrow && hasBulkActions ? withCardCheckbox(fields, view.titleField) : fields),
        [isNarrow, hasBulkActions, fields, view.titleField]
    );
    const cardSelection = useMemo(
        () => ({selection, onChangeSelection, actions}),
        [selection, onChangeSelection, actions]
    );

    return (
        <div
            ref={rootRef}
            className={cx('givewp-list-table', {
                'givewp-list-table--more-start': hasMoreStart,
                'givewp-list-table--more-end': hasMoreEnd,
                'givewp-list-table--narrow': isNarrow,
                'givewp-list-table--has-selection': selection.length > 0,
            })}
        >
            <CardSelectionContext.Provider value={cardSelection}>
                <DataViews<ListTableItem>
                    view={view}
                    onChangeView={onChangeView}
                    fields={shownFields}
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
                    // One layout at a time, so DataViews offers no layout switcher: the window width picks it.
                    defaultLayouts={isNarrow ? {grid: {}} : {table: {}}}
                >
                    <ListTableLayout
                        actions={actions}
                        data={data}
                        totalItems={paginationInfo.totalItems}
                        totalPages={paginationInfo.totalPages}
                        hasSelection={selection.length > 0}
                        isDefaultLayout={isDefaultLayout}
                        onResetLayout={onResetLayout}
                        searchBox={searchBox}
                        searchHint={searchHint}
                        filterBar={filterBar}
                        layoutMode={layoutMode}
                        viewMenu={viewMenu}
                    />
                </DataViews>
            </CardSelectionContext.Provider>
        </div>
    );
}
