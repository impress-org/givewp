import type {Action} from '@wordpress/dataviews';
import BulkActionConfirm from '../BulkActionConfirm';
import type {ListActionContext, ListTableItem, ListTableRowAction} from '../ListTablePage/types';
import {runListAction, RunListActionArgs} from '../actions/runListAction';
import useListActionSubmit from '../hooks/useListActionSubmit';
import {getItemName} from './getItemName';

/**
 * What a row action needs from the page it runs on.
 *
 * @since TBD
 */
export type RowActionContext = ListActionContext & {
    openDialog: (rowAction: ListTableRowAction, items: ListTableItem[]) => void;
};

/**
 * What `runListAction` needs to run a typed action on these items.
 */
function runArgs(rowAction: ListTableRowAction, items: ListTableItem[]): RunListActionArgs {
    return {
        run: () => rowAction.run(items),
        ids: items.map((item) => item.id),
        label: typeof rowAction.label === 'function' ? rowAction.label(items) : rowAction.label,
        successMessage: rowAction.successMessage?.(items),
    };
}

type RowActionModalProps = {
    rowAction: ListTableRowAction;
    items: ListTableItem[];
    closeModal?: () => void;
    context: RowActionContext;
};

function RowActionModal({rowAction, items, closeModal, context}: RowActionModalProps) {
    const {isBusy, submit} = useListActionSubmit(context);

    const onConfirm = () => submit({...runArgs(rowAction, items), onDone: closeModal});

    return (
        <BulkActionConfirm
            message={rowAction.confirm.body(items)}
            items={items.map((item) => ({id: item.id, name: getItemName(item)}))}
            confirmLabel={rowAction.confirm.confirmLabel}
            variant={rowAction.isDestructive ? 'danger' : 'normal'}
            isBusy={isBusy}
            onConfirm={onConfirm}
            onCancel={() => closeModal?.()}
        />
    );
}

/**
 * Maps typed actions to DataViews actions. Only an action with `supportsBulk` runs on a selection.
 *
 * - `href`: navigates to the address.
 * - `run` with `confirm`: asks first in a modal.
 * - `run` alone: runs at once.
 * - `RenderModal`: a modal body DataViews shows in its own modal.
 * - `Dialog`: a screen dialog with its own modal, opened by the page outside DataViews.
 *
 * @since TBD
 */
export function rowActionsToActions(
    rowActions: ListTableRowAction[],
    context: RowActionContext
): Action<ListTableItem>[] {
    return rowActions.map((rowAction): Action<ListTableItem> => {
        const base = {
            id: rowAction.id,
            label: rowAction.label,
            isPrimary: rowAction.isPrimary ?? false,
            isDestructive: rowAction.isDestructive ?? false,
            ...(rowAction.supportsBulk && {supportsBulk: true}),
            ...(rowAction.isEligible && {isEligible: rowAction.isEligible}),
        };

        if (rowAction.href) {
            return {...base, callback: ([item]) => window.location.assign(rowAction.href(item))};
        }

        if (rowAction.Dialog) {
            return {...base, callback: (items) => context.openDialog(rowAction, items)};
        }

        if (rowAction.RenderModal) {
            return {...base, RenderModal: rowAction.RenderModal};
        }

        if (rowAction.confirm) {
            return {
                ...base,
                modalHeader: rowAction.confirm.title,
                RenderModal: ({items, closeModal}) => (
                    <RowActionModal rowAction={rowAction} items={items} closeModal={closeModal} context={context} />
                ),
            };
        }

        return {...base, callback: (items) => runListAction(runArgs(rowAction, items), context)};
    });
}
