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

/**
 * @since TBD
 */
export type ActionDialogRequest = {rowAction: ListTableRowAction; items: ListTableItem[]};

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
 * The DataViews actions of a list screen, from its typed row actions and its bulk actions. Hidden
 * actions (`isVisible`) are left out on every render.
 *
 * Two kinds of action open a dialog outside DataViews: a typed action with `Dialog`
 * (`actionDialog`), and a `custom` bulk action (`customAction`, the last one opened; a closed
 * dialog stays mounted, as with the old table: its buttons close it mid-click).
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
    const [actionDialog, setActionDialog] = useState<ActionDialogRequest | null>(null);
    const [customAction, setCustomAction] = useState<CustomActionRequest | null>(null);

    const actions = useMemo<Action<ListTableItem>[]>(() => {
        const context = {report, refresh, clearSelection};

        return [
            ...(rowActions
                ? rowActionsToActions(
                      rowActions.filter((rowAction) => !rowAction.isVisible || rowAction.isVisible(data, parameters)),
                      {...context, openDialog: (rowAction, items) => setActionDialog({rowAction, items})}
                  )
                : []),
            ...bulkActionsToActions(bulkActions ?? [], {
                ...context,
                data,
                parameters,
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
    }, [rowActions, bulkActions, data, parameters, selection, pluralName, report, refresh, clearSelection]);

    const setCustomActionOpen = (isOpen: boolean) => setCustomAction((current) => current && {...current, isOpen});

    return {
        actions,
        actionDialog,
        closeActionDialog: () => setActionDialog(null),
        customAction,
        setCustomActionOpen,
    };
}
