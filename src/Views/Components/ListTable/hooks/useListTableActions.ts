import {useMemo, useState} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import type {Action} from '@wordpress/dataviews';
import type {
    BulkActionsConfig,
    ListActionContext,
    ListTableItem,
    ListTableParameters,
    ListTableResponse,
    ListTableRowAction,
} from '../ListTablePage/types';
import {bulkActionsToActions} from '../adapters/bulkActionsToActions';
import {rowActionsToActions} from '../adapters/rowActionsToActions';

/**
 * @since TBD
 */
export type CustomActionRequest = {config: BulkActionsConfig; items: ListTableItem[]; isOpen: boolean};

type UseListTableActionsArgs = ListActionContext & {
    rowActions: ListTableRowAction[] | null;
    bulkActions: BulkActionsConfig[] | null;
    data: ListTableResponse | undefined;
    parameters: ListTableParameters;
    /** The selected row ids, for a `custom` action that needs several rows. */
    selection: string[];
    /** The record type, plural, for the "select at least" notice. */
    pluralName: string;
};

/**
 * The DataViews actions of a list screen, from its typed row actions and its bulk actions. A
 * `custom` bulk action opens its own dialog: `customAction` holds the last one opened. A closed
 * dialog stays mounted, as with the old table: its buttons close it mid-click.
 *
 * @since TBD
 */
export default function useListTableActions({
    rowActions,
    bulkActions,
    data,
    parameters,
    selection,
    pluralName,
    report,
    refresh,
    clearSelection,
}: UseListTableActionsArgs) {
    const [customAction, setCustomAction] = useState<CustomActionRequest | null>(null);

    const actions = useMemo<Action<ListTableItem>[]>(() => {
        const context = {report, refresh, clearSelection};

        return [
            ...(rowActions ? rowActionsToActions(rowActions, context) : []),
            ...bulkActionsToActions(bulkActions ?? [], {
                ...context,
                data,
                parameters,
                openCustomAction: (config, items) => {
                    const minSelected = config.minSelected ?? 1;
                    // From a row's menu the action gets that row alone; an action that needs several rows uses the
                    // selection, without the rows the action leaves out.
                    const selectedItems = (data?.items ?? []).filter(
                        (item) =>
                            selection.includes(String(item.id)) &&
                            (!config.isIdSelectable || config.isIdSelectable(String(item.id), data))
                    );
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
    }, [rowActions, bulkActions, data, parameters, selection, pluralName, report, refresh, clearSelection]);

    const setCustomActionOpen = (isOpen: boolean) => setCustomAction((current) => current && {...current, isOpen});

    return {actions, customAction, setCustomActionOpen};
}
