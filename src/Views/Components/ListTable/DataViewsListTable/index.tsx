import {
    type ComponentProps,
    createContext,
    createElement,
    type MutableRefObject,
    type ReactNode,
    useCallback,
    useContext,
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';
import cx from 'classnames';
import {CheckboxControl, __experimentalHStack as HStack, Spinner} from '@wordpress/components';
import {DataViews} from '@wordpress/dataviews';
import {__, _n, sprintf} from '@wordpress/i18n';
import type {Action, Field, View} from '@wordpress/dataviews';
import type {ListTableItem} from '../ListTablePage/types';
import {insertNumbers} from '../formatNumber';
import ListTableViewReset from '../ListTableViewReset';
import useDelayedFlag from '../hooks/useDelayedFlag';
import useHorizontalOverflow from '../hooks/useHorizontalOverflow';
import ListTablePagination, {ArrowsPagination} from './ListTablePagination';
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
    /** The total, or `null` while it is not known: the footer then shows only the previous and next arrows. */
    paginationInfo: {totalItems: number; totalPages: number} | null;
    /** Whether a page after this one has rows. Used while the total is not known; `true` until the first answer. */
    hasNextPage?: boolean;
    isLoading: boolean;
    /** True when the request failed. The page control stays, with the last known pages, so the user can go back. */
    hasError?: boolean;
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
    // The message is translated first and the numbers go in after, so the strings stay the same as core's
    // and keep their translations. The plural uses the real number.
    const message =
        totalItems > rows
            ? insertNumbers(
                  /* translators: %1$d: number of items. %2$d: total number of items. */
                  _n('%1$d of %2$d Item', '%1$d of %2$d Items', totalItems),
                  {'%1$d': rows, '%2$d': totalItems}
              )
            : insertNumbers(
                  /* translators: %d: number of items. */
                  _n('%d Item', '%d Items', rows),
                  {'%d': rows}
              );

    return <span className="givewp-list-table__total dataviews-bulk-actions-footer__item-count">{message}</span>;
}

/**
 * Says, next to the page control, that a page is loading. The old rows stay on screen until the new ones
 * arrive (and are dimmed, see styles.scss), and the stock spinner sits under the table, out of sight when the
 * list is long, so a slow page looked like a frozen one.
 *
 * It shows only after a short wait (`isSlow`), so a fast page shows nothing. The words for screen readers
 * come later still (`announce`), so a fast page only announces its result count and the two do not talk over
 * each other. The visible text is hidden from them, so nothing is read twice.
 */
function ListTableLoading({isSlow, announce}: {isSlow: boolean; announce: boolean}) {
    return (
        <>
            {isSlow && (
                <span className="givewp-list-table__loading" aria-hidden="true">
                    <Spinner />
                    {__('Loading…', 'give')}
                </span>
            )}
            <span className="give-visually-hidden" role="status">
                {announce ? __('Loading…', 'give') : ''}
            </span>
        </>
    );
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
 * with `git log --grep`); reverting it removes all of this. The stock footer prints plain digits, so when
 * the pin moves, check that its count still follows the locale (`index.test.tsx` fails on the stock text).
 */
function ListTableFooter({
    actions,
    data,
    totalItems,
    totalPages,
    hasNextPage,
    hasSelection,
    isSlow,
    announce,
    hasError,
    view,
    onChangeView,
}: {
    actions: Action<ListTableItem>[];
    data: ListTableItem[];
    /** `null` while the total is not known. */
    totalItems: number | null;
    totalPages: number | null;
    hasNextPage: boolean;
    hasSelection: boolean;
    isSlow: boolean;
    announce: boolean;
    hasError: boolean;
    view: View;
    onChangeView: (view: View) => void;
}) {
    // The total is not known yet: only the arrows, and no total. A page past the first with no rows
    // (after an error, or past the end) still offers Previous.
    if (totalItems === null || totalPages === null) {
        if (data.length === 0 && (view.page ?? 1) <= 1) {
            return null;
        }

        return (
            <HStack expanded={false} justify="end" className="dataviews-footer givewp-list-table__footer">
                {!hasError && hasBulkActions(actions, data) && <DataViews.BulkActionToolbar />}
                <span className="givewp-list-table__spacer" />
                {!hasError && <ListTableLoading isSlow={isSlow} announce={announce} />}
                <ArrowsPagination view={view} onChangeView={onChangeView} hasNextPage={hasNextPage} />
            </HStack>
        );
    }

    // After a failed request there are no rows, but the user can still go to another page.
    if (hasError) {
        return totalPages > 1 ? (
            <HStack expanded={false} justify="end" className="dataviews-footer givewp-list-table__footer">
                <ListTablePagination view={view} onChangeView={onChangeView} totalPages={totalPages} />
            </HStack>
        ) : null;
    }

    if (totalItems <= 0 || totalPages <= 0 || data.length === 0) {
        return null;
    }

    return (
        <HStack expanded={false} justify="end" className="dataviews-footer givewp-list-table__footer">
            {hasBulkActions(actions, data) && <DataViews.BulkActionToolbar />}
            {!hasSelection && <ListTableTotal rows={data.length} totalItems={totalItems} />}
            <ListTableLoading isSlow={isSlow} announce={announce} />
            <ListTablePagination view={view} onChangeView={onChangeView} totalPages={totalPages} />
        </HStack>
    );
}

/**
 * The stock DataViews layout, with these changes: the footer shows the total row count; Give's search
 * box, filter bar and view options share one toolbar row above the table, with the search hint under
 * it; while rows are selected, the bulk actions show in the search box's place, where the selection
 * starts (the stock footer keeps its own copy next to the pagination), and the search box, its hint and
 * the view options stay there, hidden, so the table does not move; and a "Reset view" button sits after
 * the view options, which this DataViews version does not offer.
 *
 * @since TBD Render the `viewMenu` slot after "Reset view", in both layouts.
 * @since TBD In the narrow layout, put the view options on their own row under the filters, with no layout switcher.
 * @since TBD Always render the `searchBox` and `filterBar` slots; no stock search or filter row.
 * @since TBD Put the search box, the filter bar and the view options in one row, with the search hint under it; keep them in place while rows are selected.
 * @since TBD Render the `searchBox` and `filterBar` slots in place of the stock search and filters.
 * @since TBD
 */
function ListTableLayout({
    actions,
    data,
    totalItems,
    totalPages,
    hasNextPage,
    hasSelection,
    isSlow,
    announce,
    hasError,
    isDefaultLayout,
    onResetLayout,
    searchBox,
    searchHint,
    filterBar,
    layoutMode,
    viewMenu,
    view,
    onChangeView,
}: {
    actions: Action<ListTableItem>[];
    data: ListTableItem[];
    totalItems: number | null;
    totalPages: number | null;
    hasNextPage: boolean;
    hasSelection: boolean;
    isSlow: boolean;
    announce: boolean;
    hasError: boolean;
    view: View;
    onChangeView: (view: View) => void;
    isDefaultLayout: boolean;
    onResetLayout?: () => void;
    searchBox?: ReactNode;
    searchHint?: ReactNode;
    filterBar?: ReactNode;
    layoutMode: ListTableLayoutMode;
    viewMenu?: ReactNode;
}) {
    // While rows are selected, the view options, the search box and its hint stay in the layout, hidden
    // (styles.scss), so the table does not move when the selection starts or ends. Reset view comes and
    // goes, so it sits first: the view options and the menu keep their place at the end edge.
    const viewControls = (
        <HStack spacing={1} expanded={false} className="givewp-list-table__view-controls">
            {onResetLayout && <ListTableViewReset isDefault={isDefaultLayout} onConfirm={onResetLayout} />}
            <DataViews.ViewConfig />
            {viewMenu}
        </HStack>
    );
    const isNarrow = layoutMode === 'narrow';

    return (
        <>
            <div className="givewp-list-table__toolbar">
                <div
                    className={cx('givewp-list-table__toolbar-row', {
                        'givewp-list-table__toolbar-row--labeled': searchBox,
                    })}
                >
                    {(searchBox || hasSelection) && (
                        <div className="givewp-list-table__search">
                            {searchBox && <div className="givewp-list-table__search-box">{searchBox}</div>}
                            {hasSelection && (
                                <HStack justify="start" expanded={false} className="givewp-list-table__bulk-actions">
                                    <DataViews.BulkActionToolbar />
                                </HStack>
                            )}
                        </div>
                    )}
                    {filterBar}
                    {!isNarrow && viewControls}
                </div>
                {searchHint && <div className="givewp-list-table__hint">{searchHint}</div>}
            </div>
            {isNarrow && <div className="givewp-list-table__view-controls-row">{viewControls}</div>}
            {/* A zero-height box right above the table: the bar sits on the table's top edge. */}
            <div className="givewp-list-table__progress-anchor">
                {isSlow && <span className="givewp-list-table__progress" aria-hidden="true" />}
            </div>
            <DataViews.Layout />
            <ListTableFooter
                actions={actions}
                data={data}
                totalItems={totalItems}
                totalPages={totalPages}
                hasNextPage={hasNextPage}
                hasSelection={hasSelection}
                isSlow={isSlow}
                announce={announce}
                hasError={hasError}
                view={view}
                onChangeView={onChangeView}
            />
        </>
    );
}

/**
 * Renders the stock DataViews table for an admin list screen. No data fetching and no
 * Give-specific behavior inside the DataViews area.
 *
 * @since TBD Accept a `null` total and `hasNextPage`: the footer then shows only the arrows.
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
    paginationInfo: loadedPagination,
    hasNextPage = true,
    isLoading,
    hasError = false,
    selection,
    onChangeSelection: changeSelection,
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

    // A failed request has no pages of its own. The page control keeps the last known ones, for the same
    // search and filters only: another list's pages must not show. Kept in state, adjusted while rendering,
    // so nothing is written during a render that React may discard.
    const filterKey = JSON.stringify([view.search, view.filters]);
    const [lastPagination, setLastPagination] = useState<{key: string; pagination: NonNullable<typeof loadedPagination>} | null>(null);
    if (
        !hasError &&
        loadedPagination &&
        loadedPagination.totalPages > 0 &&
        (lastPagination?.key !== filterKey ||
            lastPagination.pagination.totalPages !== loadedPagination.totalPages ||
            lastPagination.pagination.totalItems !== loadedPagination.totalItems)
    ) {
        setLastPagination({key: filterKey, pagination: loadedPagination});
    }
    const lastKnownPagination = lastPagination?.key === filterKey ? lastPagination.pagination : null;
    const knownPagination = hasError && lastKnownPagination ? lastKnownPagination : loadedPagination;
    // DataViews needs numbers. Its own page control is never used without a total (see `ListTableFooter`).
    const paginationInfo = knownPagination ?? {totalItems: 0, totalPages: 0};

    // While the next page loads, the rows on screen are about to be replaced: a selection made now would be
    // thrown away, and DataViews counts it against the new rows. So selection waits for the new rows.
    const onChangeSelection = useCallback(
        (ids: string[]) => {
            if (!isLoading) {
                changeSelection(ids);
            }
        },
        [isLoading, changeSelection]
    );
    const cardSelection = useMemo(
        () => ({selection, onChangeSelection, actions}),
        [selection, onChangeSelection, actions]
    );

    // A fast page shows nothing; a slow one gets the bar and the visible text after 300 ms, and the words for
    // screen readers after 1 s.
    const isSlow = useDelayedFlag(isLoading, 300);
    const announce = useDelayedFlag(isLoading, 1000);

    // After a new page arrives, bring the top of the table into view if the user had scrolled past it (for
    // example after "Next" at the bottom of a long page). If the rows the user was on are gone and nothing
    // has the focus, the list takes it, so the keyboard does not start over from the top of the page.
    const page = view.page ?? 1;
    const pageChanged = useRef(false);
    const lastPage = useRef(page);
    useEffect(() => {
        if (lastPage.current !== page) {
            lastPage.current = page;
            pageChanged.current = true;
        }
    }, [page]);
    useEffect(() => {
        const root = rootRef.current;
        if (isLoading || !pageChanged.current || !root) {
            return;
        }

        pageChanged.current = false;

        if (root.getBoundingClientRect().top < 0) {
            root.scrollIntoView({block: 'start'});
        }

        if (document.activeElement === document.body) {
            root.focus({preventScroll: true});
        }
    }, [isLoading, data]);

    return (
        <div
            ref={rootRef}
            tabIndex={-1}
            className={cx('givewp-list-table', {
                'givewp-list-table--loading': isLoading,
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
                        totalItems={knownPagination ? knownPagination.totalItems : null}
                        totalPages={knownPagination ? knownPagination.totalPages : null}
                        hasNextPage={hasNextPage}
                        hasSelection={selection.length > 0}
                        isSlow={isSlow}
                        announce={announce}
                        hasError={hasError}
                        view={view}
                        onChangeView={onChangeView}
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
