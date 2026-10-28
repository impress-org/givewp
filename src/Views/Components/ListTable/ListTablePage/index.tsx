import {createContext, forwardRef, useCallback, useEffect, useImperativeHandle, useMemo, useState} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import type {Field, View} from '@wordpress/dataviews';
import NotificationPlaceholder from '@givewp/components/AdminDetailsPage/Notifications';
import ListTableApi from '../api';
import DataViewsListTable from '../DataViewsListTable';
import ListTableEmptyState from '../EmptyStates';
import {LEGACY_ROW_ACTIONS_FIELD} from '../LegacyRowActions';
import useLegacyRowActionsField from '../LegacyRowActions/useLegacyRowActionsField';
import {columnsToFields, titleFieldId} from '../adapters/columnsToFields';
import {filterSettingsToFields, TEST_MODE_FILTER} from '../adapters/filterSettingsToFields';
import {loadEntityElements} from '../adapters/loadEntityElements';
import {defaultFilters, scopeFromFilterSettings} from '../adapters/defaultFilters';
import {PER_PAGE_SIZES, viewFilterValue, viewToParameters, withToggleFilter} from '../adapters/viewToParameters';
import {getItemName} from '../adapters/getItemName';
import {assembleView} from '../state/assembleView';
import {keepPinnedLast, lockFields, placeShownFields} from '../state/fieldRules';
import {readStoredLayout} from '../state/layoutApi';
import {applyStoredLayout} from '../state/storedLayout';
import {resolveScreenDefaults} from '../state/screenDefaults';
import type {ViewQueryContext} from '../state/types';
import useListTableView from '../hooks/useListTableView';
import useListTableUrlState, {readLegacyUrlState, readUrlState, urlScreenKey} from '../hooks/useListTableUrlState';
import useListTableLayoutPreferences from '../hooks/useListTableLayoutPreferences';
import useListTableSelection from '../hooks/useListTableSelection';
import useListTableQuery from '../hooks/useListTableQuery';
import useListTableActionNotices from '../hooks/useListTableActionNotices';
import useListTableActions from '../hooks/useListTableActions';
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
    FilterByGroupedOptions,
    FilterConfig,
    LegacyRowActionArgs,
    LegacyRowActions,
    ListTableActionDialogProps,
    ListTableItem,
    ListTablePageProps,
    ListTableParameters,
    ListTablePageRef,
    ListTableRowAction,
} from './types';

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
            columnFilters = [],
            banner,
            contentMode = false,
            perPage = 30,
            statsConfig,
            screen,
        }: ListTablePageProps,
        ref
    ) => {
        const columns = apiSettings.table.columns as Column[];
        const legacyRowActions = typeof rowActions === 'function' ? (rowActions as LegacyRowActionsRender) : null;
        const typedRowActions = Array.isArray(rowActions) ? (rowActions as ListTableRowAction[]) : null;
        const testModeFilter = filterSettings.find((filter) => filter.name === 'toggle');
        const locale = navigator.language || navigator.languages[0];

        // Read once: some screens' header modals rewrite `apiSettings.apiRoot` while they render.
        const [apiRoot] = useState(apiSettings.apiRoot);
        const [listTableApi] = useState(() => new ListTableApi(apiSettings));
        const [scope] = useState(() => scopeFromFilterSettings(filterSettings, contentMode));
        const [legacyConfirm, setLegacyConfirm] = useState<LegacyConfirmRequest | null>(null);

        // Resolved once, before the first render, so the first list request already carries the URL's view.
        const [start] = useState(() => {
            const defaults = resolveScreenDefaults(screen, columns, titleFieldId(columns), {
                perPage,
                pinnedField: legacyRowActions ? LEGACY_ROW_ACTIONS_FIELD : undefined,
                filters: defaultFilters(filterSettings, {contentMode, testMode: Boolean(paymentMode)}),
            });
            const context: ViewQueryContext = {
                filterSettings,
                sortableFields: columns.filter((column) => column.sortable).map((column) => column.id),
                defaults,
                siteTestMode: Boolean(paymentMode),
            };
            const screenKey = urlScreenKey(screen, contentMode);

            const urlState = screenKey
                ? readUrlState(screenKey, context)
                : contentMode
                ? undefined
                : readLegacyUrlState(context);

            return {
                defaults,
                context,
                layout: screen ? applyStoredLayout(defaults, readStoredLayout(screen.key)) : undefined,
                urlState,
            };
        });

        const {view, origin, onChangeView, replaceView, hasActiveFilters, clearFilters} = useListTableView(() =>
            assembleView({
                defaults: start.defaults,
                layout: start.layout,
                query: start.urlState?.query,
                search: start.urlState?.search,
            })
        );

        useListTableUrlState({
            screen,
            contentMode,
            context: start.context,
            defaults: start.defaults,
            initial: start.urlState,
            view,
            origin,
            replaceView,
        });
        const {resetLayout, isDefaultLayout} = useListTableLayoutPreferences({
            screen,
            defaults: start.defaults,
            view,
            origin,
            replaceView,
        });
        // A screen with the test-mode toggle keeps test mode in the view, so it shows as a chip.
        const testMode = testModeFilter ? viewFilterValue(view, TEST_MODE_FILTER) === true : paymentMode;
        const setTestMode = (isOn: boolean) => onChangeView(withToggleFilter(view, TEST_MODE_FILTER, isOn));
        const {selection, onChangeSelection} = useListTableSelection(view);
        const clearSelection = useCallback(() => onChangeSelection([]), [onChangeSelection]);
        const report = useListTableActionNotices(singleName, pluralName);

        // "Filter by" values such as "Period" count from this time; fixed per view so the request stays the same.
        // Only what changes the request restarts the clock, not a layout change such as density or columns.
        const requestKey = JSON.stringify([view.page, view.perPage, view.sort, view.filters, view.search]);
        const requestTime = useMemo(() => new Date(), [requestKey]);
        const parameters = useMemo(
            () => viewToParameters(view, filterSettings, scope, {locale, testMode, now: requestTime}),
            [view, filterSettings, scope, locale, testMode, requestTime]
        );

        const {
            data,
            error,
            isLoading,
            refresh: refreshList,
        } = useListTableQuery<ListTableResponse>(apiRoot, parameters);
        const {data: stats, refresh: refreshStats} = useListTableQuery<Record<string, number>>(
            statsConfig ? `${apiRoot}/stats` : null,
            {testMode}
        );

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

        const filters = useMemo(
            () => filterSettingsToFields(filterSettings, view, {contentMode, loadEntityElements}),
            [filterSettings, view, contentMode]
        );

        const fields = useMemo<Field<ListTableItem>[]>(
            () =>
                lockFields(
                    [
                        ...columnsToFields(columns, columnFilters, data),
                        ...(legacyRowActionsField ? [legacyRowActionsField] : []),
                        ...filters.fields,
                    ],
                    start.defaults
                ),
            [columns, columnFilters, data, legacyRowActionsField, filters]
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
                            testModeFilter={testModeFilter}
                            testMode={testMode}
                            onChangeTestMode={setTestMode}
                            statsConfig={statsConfig}
                            stats={stats}
                        >
                            {children}
                        </ListTablePageHeader>
                    )}

                    <div className={styles.pageContent}>
                        {contentMode && children}
                        <NotificationPlaceholder type="notice" />
                        {productRecommendation && (
                            <table className="givewp-list-table-recommendation">
                                <tbody>{productRecommendation}</tbody>
                            </table>
                        )}
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
                                ) : hasActiveFilters ? (
                                    <ListTableEmptyState
                                        message={sprintf(
                                            /* translators: %s: record type, plural (for example "donations") */
                                            __('No %s match your search or filters.', 'give'),
                                            pluralName
                                        )}
                                        actionLabel={__('Clear filters', 'give')}
                                        onAction={clearFilters}
                                    />
                                ) : (
                                    listTableBlankSlate
                                )
                            }
                            perPageSizes={PER_PAGE_SIZES}
                            search={filters.searchParam !== null}
                            searchLabel={
                                filterSettings.find((filter) => filter.name === filters.searchParam)?.text ??
                                __('Search', 'give')
                            }
                            isDefaultLayout={isDefaultLayout}
                            onResetLayout={resetLayout}
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
