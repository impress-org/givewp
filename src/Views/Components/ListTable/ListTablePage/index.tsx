import {createContext, forwardRef, useCallback, useEffect, useImperativeHandle, useMemo, useState} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import type {Field} from '@wordpress/dataviews';
import cx from 'classnames';
import {GiveIcon} from '@givewp/components';
import NotificationPlaceholder from '@givewp/components/AdminDetailsPage/Notifications';
import ToggleSwitch from '@givewp/components/ListTable/ToggleSwitch';
import ListTableApi from '../api';
import ListTableStats from '../ListTableStats/ListTableStats';
import DataViewsListTable from '../DataViewsListTable';
import ListTableErrorState from '../ListTableErrorState';
import FilteredEmptyState from '../EmptyStates/FilteredEmptyState';
import LegacyRowActions, {LEGACY_ROW_ACTIONS_FIELD} from '../LegacyRowActions';
import {columnsToFields, titleFieldId, visibleFieldIds} from '../adapters/columnsToFields';
import {filterSettingsToFields} from '../adapters/filterSettingsToFields';
import {loadEntityElements} from '../adapters/loadEntityElements';
import {scopeFromFilterSettings, viewFromInitialState} from '../adapters/viewFromInitialState';
import {PER_PAGE_SIZES, viewToParameters} from '../adapters/viewToParameters';
import {bulkActionsToActions} from '../adapters/bulkActionsToActions';
import {rowActionsToActions} from '../adapters/rowActionsToActions';
import {normalizeActionResult} from '../adapters/normalizeActionResult';
import {getItemName} from '../adapters/getItemName';
import useListTableView from '../hooks/useListTableView';
import useListTableSelection from '../hooks/useListTableSelection';
import useListTableQuery from '../hooks/useListTableQuery';
import useListTableActionNotices from '../hooks/useListTableActionNotices';
import LegacyConfirmModal, {LegacyConfirmRequest} from './LegacyConfirmModal';
import type {
    BulkActionsConfig,
    Column,
    LegacyRowActionArgs,
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
    ListTableItem,
    ListTablePageProps,
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
 * @deprecated TBD The list engine owns selection. Only the old table code, removed with it, reads this.
 */
export const CheckboxContext = createContext(null);

/**
 * The record id a legacy row action button carries in `data-actionid`.
 */
const actionIdOf = (event: {target: EventTarget}) =>
    (event.target as HTMLElement).closest<HTMLElement>('[data-actionid]')?.dataset.actionid;

/**
 * The page shell every admin list screen renders through: the Give page header and stats, and the
 * list itself through the WordPress list engine (`@wordpress/dataviews`). It accepts the same props
 * as before the engine change, so every screen and add-on keeps rendering without changes.
 *
 * @since TBD Render the list through `@wordpress/dataviews`; accept typed row actions.
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
        const [urlSearch] = useState(() => new URLSearchParams(window.location.search));
        const [scope] = useState(() => scopeFromFilterSettings(filterSettings, contentMode, urlSearch));
        const [testMode, setTestMode] = useState(paymentMode);
        const [legacyConfirm, setLegacyConfirm] = useState<LegacyConfirmRequest | null>(null);
        const [customAction, setCustomAction] = useState<{
            config: BulkActionsConfig;
            items: ListTableItem[];
            isOpen: boolean;
        } | null>(null);

        const {view, onChangeView, hasActiveFilters, clearFilters} = useListTableView(() => {
            const titleField = titleFieldId(columns);
            const fields = [...visibleFieldIds(columns, titleField), ...(legacyRowActions ? [LEGACY_ROW_ACTIONS_FIELD] : [])];

            return viewFromInitialState(filterSettings, {perPage, fields, titleField, contentMode}, urlSearch);
        });
        const {selection, onChangeSelection} = useListTableSelection(view);
        const report = useListTableActionNotices(singleName, pluralName);

        const parameters = useMemo(
            () => viewToParameters(view, filterSettings, scope, {locale, testMode}),
            [view, filterSettings, scope, locale, testMode]
        );

        const {data, error, isLoading, refresh: refreshList} = useListTableQuery<ListTableResponse>(apiRoot, parameters);
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
                onChangeView({...view, page: data.totalPages});
            }
        }, [data]);

        const legacyRowActionArgs = useCallback(
            (item: ListTableItem): LegacyRowActionArgs => ({
                data,
                item,
                parameters,
                listTableApi,
                removeRow: (callback) => async (event) => {
                    await callback(actionIdOf(event));
                    await refresh();
                },
                addRow: (callback) => async (event) => {
                    await callback(actionIdOf(event));
                    await refresh();
                },
                setUpdateErrors: (response) => {
                    report(normalizeActionResult(response, []));
                    refresh();
                },
            }),
            [data, parameters, listTableApi, refresh, report]
        );

        const filters = useMemo(
            () => filterSettingsToFields(filterSettings, view, {contentMode, loadEntityElements}),
            [filterSettings, view, contentMode]
        );

        const fields = useMemo<Field<ListTableItem>[]>(
            () => [
                ...columnsToFields(columns, columnFilters, data),
                ...(legacyRowActions
                    ? [
                          {
                              id: LEGACY_ROW_ACTIONS_FIELD,
                              label: __('Actions', 'give'),
                              enableHiding: false,
                              enableSorting: false,
                              render: ({item}) => (
                                  <LegacyRowActions render={legacyRowActions} args={legacyRowActionArgs(item)} />
                              ),
                          },
                      ]
                    : []),
                ...filters.fields,
            ],
            [columns, columnFilters, data, legacyRowActions, legacyRowActionArgs, filters]
        );

        const actions = useMemo(() => {
            // Add-ons add Donations bulk actions here before the screen mounts.
            const addonBulkActions = bulkActions ? window.GiveDonations?.addonsBulkActions ?? [] : [];

            return [
                ...(typedRowActions
                    ? rowActionsToActions(typedRowActions, {report, refresh, clearSelection: () => onChangeSelection([])})
                    : []),
                ...bulkActionsToActions([...(bulkActions ?? []), ...addonBulkActions], {
                    data,
                    parameters,
                    report,
                    refresh,
                    clearSelection: () => onChangeSelection([]),
                    openCustomAction: (config, items) => {
                        const minSelected = config.minSelected ?? 1;
                        // From a row's menu the action gets that row alone; an action that needs several rows uses the selection.
                        const selectedItems = (data?.items ?? []).filter((item) => selection.includes(String(item.id)));
                        const actionItems = items.length < minSelected ? selectedItems : items;

                        if (actionItems.length < minSelected) {
                            report({
                                changed: 0,
                                failed: 0,
                                notice: sprintf(
                                    /* translators: 1: number of records, 2: record type, plural */
                                    __('Select at least %1$d %2$s to use this action.', 'give'),
                                    minSelected,
                                    pluralName
                                ),
                            });
                            return;
                        }

                        setCustomAction({config, items: actionItems, isOpen: true});
                    },
                }),
            ];
        }, [typedRowActions, bulkActions, data, parameters, report, refresh, selection, onChangeSelection, pluralName]);

        const showConfirmModal = useCallback(
            (label, confirm, action, type = null, confirmButtonText = __('Confirm', 'give')) =>
                setLegacyConfirm({label, confirm, action, type, confirmButtonText}),
            []
        );

        return (
            <ShowConfirmModalContext.Provider value={showConfirmModal}>
                <article className={styles.page}>
                    {!contentMode && (
                        <>
                            <header className={styles.pageHeader}>
                                <div className={styles.flexRow}>
                                    <GiveIcon size={'2.25rem'} />
                                    <h1 className={styles.pageTitle}>{title}</h1>
                                    {testModeFilter && testMode && (
                                        <span className={styles.testModeBadge}>{testModeFilter.text}</span>
                                    )}
                                </div>
                                {children && <div className={styles.flexRow}>{children}</div>}
                            </header>

                            <div className={cx('wp-header-end', 'hidden')} />

                            {banner && <section role="banner">{banner()}</section>}
                            {testModeFilter && (
                                <div className={styles.filtersRow}>
                                    <ToggleSwitch
                                        ariaLabel={testModeFilter.ariaLabel}
                                        onChange={setTestMode}
                                        checked={testMode}
                                    />
                                </div>
                            )}
                            {statsConfig && stats && <ListTableStats config={statsConfig} values={stats} />}
                        </>
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
                            onChangeView={onChangeView}
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
                                    <ListTableErrorState pluralName={pluralName} onRetry={refresh} />
                                ) : hasActiveFilters ? (
                                    <FilteredEmptyState pluralName={pluralName} onClearFilters={clearFilters} />
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

                {/* A closed custom dialog stays mounted, as with the old table: its buttons close it mid-click. */}
                {customAction &&
                    customAction.config.confirm(
                        customAction.items.map((item) => String(item.id)),
                        customAction.items.map(getItemName),
                        customAction.isOpen,
                        (isOpen = false) => setCustomAction((current) => current && {...current, isOpen}),
                        {refresh, clearSelection: () => onChangeSelection([])}
                    )}
            </ShowConfirmModalContext.Provider>
        );
    }
);

ListTablePage.displayName = 'ListTablePage';

export default ListTablePage;
