import {useMemo, useState} from 'react';
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
export type CustomActionRequest = {config: BulkActionsConfig; items: ListTableItem[]};

type UseListTableActionsArgs = ListActionContext & {
    rowActions: ListTableRowAction[] | null;
    bulkActions: BulkActionsConfig[] | null;
    data: ListTableResponse | undefined;
    parameters: ListTableParameters;
};

/**
 * The DataViews actions of a list screen, from its typed row actions and its bulk actions. A
 * `custom` bulk action opens its own dialog: `customAction` holds the one open now.
 *
 * @since TBD
 */
export default function useListTableActions({
    rowActions,
    bulkActions,
    data,
    parameters,
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
                openCustomAction: (config, items) => setCustomAction({config, items}),
            }),
        ];
    }, [rowActions, bulkActions, data, parameters, report, refresh, clearSelection]);

    return {actions, customAction, closeCustomAction: () => setCustomAction(null)};
}
