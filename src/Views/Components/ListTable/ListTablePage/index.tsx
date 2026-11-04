import {createContext, forwardRef, useCallback, useEffect, useImperativeHandle, useMemo, useRef, useState} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import type {Field, View} from '@wordpress/dataviews';
import NotificationPlaceholder from '@givewp/components/AdminDetailsPage/Notifications';
import ListTableApi from '../api';
import DataViewsListTable from '../DataViewsListTable';
import ListTableEmptyState from '../EmptyStates';
import ListTableFilters from '../ListTableFilters';
import ListTableNoticeArea from '../ListTableNoticeArea';
import ListTableSearch from '../ListTableSearch';
import ResultCountStatus from '../ResultCountStatus';
import {isBarFilter} from '../filters/filterKinds';
import {LEGACY_ROW_ACTIONS_FIELD} from '../LegacyRowActions';
import useLegacyRowActionsField from '../LegacyRowActions/useLegacyRowActionsField';
import {columnsToFields, titleFieldId} from '../adapters/columnsToFields';
import {
    DEFAULT_PER_PAGE,
    filterFieldId,
    PER_PAGE_SIZES,
    TEST_MODE_FILTER,
    viewFilterValue,
    viewToParameters,
    withToggleFilter,
} from '../adapters/viewToParameters';
import {getItemName} from '../adapters/getItemName';
import {filterSettingsToDeclarations} from '../filters/legacyFilterSettings';
import {siteToday} from '../filters/siteDate';
import {differsFromDefaultFilters, differsFromDefaultFiltersIgnoring} from '../state/defaultFilterState';
import type {BarFilterDeclaration, FilterDeclaration, SearchFilterDeclaration} from '../filters/types';
import {assembleView} from '../state/assembleView';
import {keepPinnedLast, lockFields, placeShownFields} from '../state/fieldRules';
import {resolveScreenDefaults} from '../state/screenDefaults';
import type {ViewQueryContext} from '../state/types';
import useListTableView from '../hooks/useListTableView';
import useListTableUrlState, {readInitialUrlState} from '../hooks/useListTableUrlState';
import useListTableLayoutPreferences, {readInitialLayout} from '../hooks/useListTableLayoutPreferences';
import useListTableSelection from '../hooks/useListTableSelection';
import useListTableQuery from '../hooks/useListTableQuery';
import useListTableStats from '../hooks/useListTableStats';
import useListTableActionNotices from '../hooks/useListTableActionNotices';
import useListTableActions from '../hooks/useListTableActions';
import useDailyRecommendation from '../hooks/useDailyRecommendation';
import ListTablePageHeader from './ListTablePageHeader';
import LegacyConfirmModal, {LegacyConfirmRequest} from './LegacyConfirmModal';
import type {
    Column,
    LegacyRowActions as LegacyRowActionsRender,
    ListTableItem,
    ListTablePageProps,
    ListTablePageRef,
    ListTableResponse,
    ListTableRowAction,
} from './types';
import styles from './ListTablePage.module.scss';
import './styles.scss';

export type {
    BulkActionsConfig,
    BulkActionsConfigWithoutAction,
    Column,
    ColumnFilterConfig,
    FilterConfig,
    LegacyRowActionArgs,
    LegacyRowActions,
    ListTableActionDialogProps,
    ListTableItem,
    ListTablePageProps,
    ListTableParameters,
    ListTablePageRef,
    ListTableRowAction,
    StatConfig,
    TestModeToggle,
} from './types';

const isSearch = (declaration: FilterDeclaration): declaration is SearchFilterDeclaration =>
    declaration.kind === 'search';

/**
 * Opens the confirm modal for a legacy row action.
 *
 * @since TBD Opens the list engine's confirm modal.
 */
export const ShowConfirmModalContext = createContext(
    (label, confirm, action, type = null, confirmButtonText = __('Confirm', 'give')) => {}
);

/**
 * The page shell every admin list screen renders through: the Give page header and stats, and the
 * list itself through the WordPress list engine (`@wordpress/dataviews`). It accepts the same props
 * as before the engine change, so every screen and add-on keeps rendering without changes.
 *
 * @since TBD Keep the stat tiles mounted with a status, and name their region by the screen.
 * @since TBD Send an explicit `scope` also without `filters`, over the scope from `filterSettings`.
 * @since TBD Announce a cached result count only after its request has finished.
 * @since TBD Let the stat tiles toggle the single-choice option filters the screen declares.
 * @since TBD After Reset, focus the search box, else a control left in the filter bar, else the bar.
 * @since TBD Announce the result count to screen readers after the admin changes the view.
 * @since TBD Do not send the browser locale with list requests.
 * @since TBD One filter path: the deprecated `filterSettings` are mapped onto filter declarations, and the DataViews filter row and search are gone.
 * @since TBD With `filters`, show Reset only when the filters differ from the screen defaults, and reset to those defaults.
 * @since TBD Accept filter declarations (`filters`), a fixed `scope` and `testModeToggle`; with `filters`, render Give's filter bar and labeled search.
 * @since TBD Render the list through `@wordpress/dataviews`; accept typed row actions; keep the view in the URL; store and reset the layout.
 * @since 2.24.0
 */
const ListTablePage = forwardRef<ListTablePageRef, ListTablePageProps>(
    (
        {
            title,
            apiSettings,
            bulkActions = null,
            filterSettings = [],
            singleName = __('item', 'give'),
            pluralName = __('items', 'give'),
            rowActions = null,
            children = null,
            paymentMode,
            listTableBlankSlate,
            productRecommendation,
            notices,
            recommendations,
            columnFilters = [],
            banner,
            contentMode = false,
            perPage = DEFAULT_PER_PAGE,
            statsConfig,
            screen,
            filters: declaredFilters,
            scope: fixedScope,
            testModeToggle,
        }: ListTablePageProps,
        ref
    ) => {
        const columns = apiSettings.table.columns as Column[];
        const legacyRowActions = typeof rowActions === 'function' ? (rowActions as LegacyRowActionsRender) : null;
        const typedRowActions = Array.isArray(rowActions) ? (rowActions as ListTableRowAction[]) : null;
        // A screen that passes only the deprecated filter settings gets them mapped onto declarations, once.
        const [legacy] = useState(() =>
            declaredFilters ? undefined : filterSettingsToDeclarations(filterSettings, contentMode)
        );
        const declarations = declaredFilters ?? legacy?.filters ?? [];
        const testModeSwitch = testModeToggle ?? legacy?.testModeToggle;
        const statuses = apiSettings.table.statuses ?? [];

        // Read once: some screens' header modals rewrite `apiSettings.apiRoot` while they render.
        const [apiRoot] = useState(apiSettings.apiRoot);
        const [listTableApi] = useState(() => new ListTableApi(apiSettings));
        // An explicit `scope` goes over the scope that a tab's hidden (deprecated) filter settings give.
        const [scope] = useState(() => ({...legacy?.scope, ...fixedScope}));
        const [legacyConfirm, setLegacyConfirm] = useState<LegacyConfirmRequest | null>(null);

        // Resolved once, before the first render, so the first list request already carries the URL's view.
        const [start] = useState(() => {
            const defaults = resolveScreenDefaults(screen, columns, titleFieldId(columns), {
                perPage,
                pinnedField: legacyRowActions ? LEGACY_ROW_ACTIONS_FIELD : undefined,
                // Declared filters have no defaults: only test mode, on a test site, and a standalone
                // hidden filter of the old settings.
                filters: [
                    ...(legacy?.defaultFilters ?? []),
                    ...(testModeSwitch && paymentMode
                        ? [{field: filterFieldId(TEST_MODE_FILTER), operator: 'is' as const, value: true}]
                        : []),
                ],
            });
            const context: ViewQueryContext = {
                filters: declarations,
                statuses,
                testModeToggle: Boolean(testModeSwitch),
                sortableFields: columns.filter((column) => column.sortable).map((column) => column.id),
                defaults,
                siteTestMode: Boolean(paymentMode),
            };

            return {
                defaults,
                context,
                layout: readInitialLayout(screen, defaults),
                urlState: readInitialUrlState(screen, contentMode, context),
            };
        });

        const {view, origin, onChangeView, replaceView, restoreDefaultFilters} = useListTableView(() =>
            assembleView({
                defaults: start.defaults,
                layout: start.layout,
                query: start.urlState?.query,
                search: start.urlState?.search,
            })
        );

        const {resetLayout, isDefaultLayout, layoutPerPage} = useListTableLayoutPreferences({
            screen,
            defaults: start.defaults,
            initialLayout: start.layout,
            view,
            origin,
            replaceView,
        });
        useListTableUrlState({
            screen,
            contentMode,
            context: start.context,
            defaults: start.defaults,
            initial: start.urlState,
            layoutPerPage,
            view,
            origin,
            replaceView,
        });
        // A screen with the test-mode toggle keeps test mode in the view, so it shows as a chip.
        const testMode = testModeSwitch ? viewFilterValue(view, TEST_MODE_FILTER) === true : paymentMode;
        // Only a single-choice list can be set by a tile.
        const tileFilterIds = useMemo(
            () =>
                declarations
                    .filter((declaration) => declaration.kind === 'options' && !declaration.multiple)
                    .map((declaration) => declaration.id),
            [declarations]
        );

        const setTestMode = (isOn: boolean) => onChangeView(withToggleFilter(view, TEST_MODE_FILTER, isOn));
        const {selection, onChangeSelection} = useListTableSelection(view);
        const clearSelection = useCallback(() => onChangeSelection([]), [onChangeSelection]);
        const report = useListTableActionNotices(singleName, pluralName);

        // Date presets count from the site's day, fixed per view so the request stays the same. Only what
        // changes the request reads the day again, not a layout change such as density or columns.
        const requestKey = JSON.stringify([view.page, view.perPage, view.sort, view.filters, view.search]);
        const today = useMemo(() => siteToday(), [requestKey]);
        const parameters = useMemo(
            () => viewToParameters(view, declarations, scope, {testMode, today}),
            [view, declarations, scope, testMode, today]
        );

        const {
            data,
            error,
            isLoading,
            isValidating,
            refresh: refreshList,
        } = useListTableQuery<ListTableResponse>(apiRoot, parameters);
        const {
            stats,
            status: statsStatus,
            refresh: refreshStats,
        } = useListTableStats(statsConfig ? `${apiRoot}/stats` : null, testMode);

        const refresh = useCallback(async () => {
            await Promise.all([refreshList(), statsConfig && refreshStats()]);
        }, [refreshList, refreshStats, statsConfig]);

        useImperativeHandle(ref, () => ({refresh}), [refresh]);

        // After records are removed, the current page can be past the last one.
        useEffect(() => {
            if (data && data.totalPages > 0 && view.page > data.totalPages) {
                replaceView({...view, page: data.totalPages}, 'correction');
            }
        }, [data]);

        const legacyRowActionsField = useLegacyRowActionsField(legacyRowActions, {
            data,
            parameters,
            listTableApi,
            report,
            refresh,
        });

        const searchDeclaration = declarations.find(isSearch);
        // Reset shows only when the view differs from the screen's defaults, so an untouched view on a
        // test site (test mode on by default) offers no Reset, and Reset puts the defaults back instead
        // of turning test mode off. An added filter with no value yet counts.
        const canReset = differsFromDefaultFilters(view, start.defaults.filters);
        const searchBoxRef = useRef<HTMLDivElement>(null);
        const filterBarRef = useRef<HTMLDivElement>(null);
        // The search box, or, when it is not shown (during a bulk selection, or on a screen without a
        // search), the first control left in the filter bar, or else the bar itself.
        const focusFilterArea = useCallback(() => {
            const bar = filterBarRef.current;
            const target =
                searchBoxRef.current?.querySelector('input') ??
                bar?.querySelector<HTMLElement>('button:not(:disabled)') ??
                bar;

            target?.focus();
        }, []);
        // The control that was clicked (Reset, Clear filters) goes away, so keyboard focus moves once the
        // page has re-rendered without it.
        const [focusRequest, setFocusRequest] = useState(0);
        useEffect(() => {
            if (focusRequest > 0) {
                focusFilterArea();
            }
        }, [focusRequest, focusFilterArea]);
        const resetFilters = useCallback(() => {
            restoreDefaultFilters(start.defaults.filters);
            setFocusRequest((count) => count + 1);
        }, [restoreDefaultFilters, start.defaults.filters]);
        // The filtered empty state offers "Clear filters", so it shows only when a search or a filter
        // narrows the list. Test mode does not count: an empty list with only test mode changed is
        // just empty, so it shows the screen's own blank slate.
        const isFilteredEmpty = differsFromDefaultFiltersIgnoring(
            view,
            start.defaults.filters,
            filterFieldId(TEST_MODE_FILTER)
        );

        const fields = useMemo<Field<ListTableItem>[]>(
            () =>
                lockFields(
                    [
                        ...columnsToFields(columns, columnFilters, data),
                        ...(legacyRowActionsField ? [legacyRowActionsField] : []),
                    ],
                    start.defaults
                ),
            [columns, columnFilters, data, legacyRowActionsField]
        );
        const onChangeTableView = useCallback(
            (next: View) => onChangeView(keepPinnedLast(placeShownFields(next, view, start.defaults), start.defaults)),
            [onChangeView, view]
        );

        const {actions, actionDialog, closeActionDialog, customAction, setCustomActionOpen} = useListTableActions({
            rowActions: typedRowActions,
            bulkActions,
            data,
            parameters,
            selection,
            pluralName,
            report,
            refresh,
            clearSelection,
        });

        const {recommendation, dismiss: dismissRecommendation} = useDailyRecommendation(recommendations);

        const showConfirmModal = useCallback(
            (label, confirm, action, type = null, confirmButtonText = __('Confirm', 'give')) =>
                setLegacyConfirm({label, confirm, action, type, confirmButtonText}),
            []
        );

        return (
            <ShowConfirmModalContext.Provider value={showConfirmModal}>
                <article className={styles.page}>
                    {!contentMode && (
                        <ListTablePageHeader
                            title={title}
                            banner={banner}
                            testModeToggle={testModeSwitch}
                            testMode={testMode}
                            onChangeTestMode={setTestMode}
                            statsConfig={statsConfig}
                            stats={stats}
                            statsStatus={statsStatus}
                            filters={view.filters}
                            availableFilterIds={tileFilterIds}
                            onChangeFilters={(filters) => onChangeView({...view, filters, page: 1})}
                        >
                            {children}
                        </ListTablePageHeader>
                    )}

                    <div className={styles.pageContent}>
                        {contentMode && children}
                        <ListTableNoticeArea
                            notices={notices ?? []}
                            recommendation={recommendation}
                            onDismissRecommendation={dismissRecommendation}
                            trailing={
                                productRecommendation && (
                                    <table className="givewp-list-table-recommendation">
                                        <tbody>{productRecommendation}</tbody>
                                    </table>
                                )
                            }
                        >
                            <NotificationPlaceholder type="notice" />
                        </ListTableNoticeArea>
                        <DataViewsListTable
                            view={view}
                            onChangeView={onChangeTableView}
                            fields={fields}
                            actions={actions}
                            data={error ? [] : data?.items ?? []}
                            paginationInfo={{
                                totalItems: Number(data?.totalItems ?? 0),
                                totalPages: data?.totalPages ?? 0,
                            }}
                            isLoading={isLoading}
                            selection={selection}
                            onChangeSelection={onChangeSelection}
                            empty={
                                error ? (
                                    <ListTableEmptyState
                                        isError
                                        message={sprintf(
                                            /* translators: %s: record type, plural (for example "donations") */
                                            __('There was a problem retrieving the %s.', 'give'),
                                            pluralName
                                        )}
                                        actionLabel={__('Try again', 'give')}
                                        onAction={refresh}
                                    />
                                ) : isFilteredEmpty ? (
                                    <ListTableEmptyState
                                        message={sprintf(
                                            /* translators: %s: record type, plural (for example "donations") */
                                            __('No %s match your search or filters.', 'give'),
                                            pluralName
                                        )}
                                        actionLabel={__('Clear filters', 'give')}
                                        onAction={resetFilters}
                                    />
                                ) : (
                                    listTableBlankSlate
                                )
                            }
                            perPageSizes={PER_PAGE_SIZES}
                            isDefaultLayout={isDefaultLayout}
                            onResetLayout={resetLayout}
                            searchBox={
                                searchDeclaration && (
                                    <div ref={searchBoxRef}>
                                        <ListTableSearch
                                            label={searchDeclaration.label}
                                            hint={searchDeclaration.hint}
                                            value={view.search ?? ''}
                                            onChange={(term) => onChangeView({...view, search: term})}
                                        />
                                    </div>
                                )
                            }
                            filterBar={
                                <ListTableFilters
                                    declarations={declarations.filter(isBarFilter)}
                                    view={view}
                                    onChangeView={onChangeView}
                                    statuses={statuses}
                                    screen={screen?.key}
                                    canReset={canReset}
                                    onReset={resetFilters}
                                    containerRef={filterBarRef}
                                />
                            }
                        />
                        <ResultCountStatus
                            // The request itself, so a filter added with no value yet is not a new result.
                            requestKey={JSON.stringify(parameters)}
                            isLoading={isLoading}
                            isValidating={isValidating}
                            totalItems={error || !data ? undefined : Number(data.totalItems ?? 0)}
                            singleName={singleName}
                            pluralName={pluralName}
                        />
                    </div>
                </article>

                {/* Details pages that hold a list in a tab already show snackbars. */}
                {!contentMode && <NotificationPlaceholder type="snackbar" />}

                {legacyConfirm && (
                    <LegacyConfirmModal
                        request={legacyConfirm}
                        onClose={() => setLegacyConfirm(null)}
                        report={report}
                        refresh={refresh}
                    />
                )}

                {actionDialog && (
                    <actionDialog.rowAction.Dialog
                        items={actionDialog.items}
                        onClose={closeActionDialog}
                        refresh={refresh}
                        report={report}
                        clearSelection={clearSelection}
                    />
                )}

                {/* A closed custom dialog stays mounted, as with the old table: its buttons close it mid-click. */}
                {customAction &&
                    customAction.config.confirm(
                        customAction.items.map((item) => String(item.id)),
                        customAction.items.map(getItemName),
                        customAction.isOpen,
                        (isOpen = false) => setCustomActionOpen(isOpen),
                        {refresh, clearSelection}
                    )}
            </ShowConfirmModalContext.Provider>
        );
    }
);

ListTablePage.displayName = 'ListTablePage';

export default ListTablePage;
