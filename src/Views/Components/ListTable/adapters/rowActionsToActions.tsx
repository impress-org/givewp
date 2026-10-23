import type {Action} from '@wordpress/dataviews';
import BulkActionConfirm from '../BulkActionConfirm';
import type {ListActionContext, ListTableItem, ListTableRowAction} from '../ListTablePage/types';
import {runListAction} from '../actions/runListAction';
import useListActionSubmit from '../hooks/useListActionSubmit';
import {getItemName} from './getItemName';

/**
 * What a row action needs from the page it runs on.
 *
 * @since TBD
 */
export type RowActionContext = ListActionContext;


type RowActionModalProps = {
    rowAction: ListTableRowAction;
    item: ListTableItem;
    closeModal?: () => void;
    context: RowActionContext;
};

function RowActionModal({rowAction, item, closeModal, context}: RowActionModalProps) {
    const {isBusy, submit} = useListActionSubmit(context);

    const onConfirm = () =>
        submit({run: () => rowAction.run(item), ids: [item.id], label: rowAction.label, onDone: closeModal});

    return (
        <BulkActionConfirm
            message={rowAction.confirm.body(item)}
            items={[{id: item.id, name: getItemName(item)}]}
            confirmLabel={rowAction.confirm.confirmLabel}
            variant={rowAction.isDestructive ? 'danger' : 'normal'}
            isBusy={isBusy}
            onConfirm={onConfirm}
            onCancel={() => closeModal?.()}
        />
    );
}

/**
 * Maps typed row actions to DataViews actions. A row action never runs on a selection.
 *
 * - `href`: navigates to the address.
 * - `run` with `confirm`: asks first in a modal.
 * - `run` alone: runs at once.
 * - `RenderModal`: the screen's own modal.
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
            ...(rowAction.isEligible && {isEligible: rowAction.isEligible}),
        };

        if (rowAction.href) {
            return {...base, callback: ([item]) => window.location.assign(rowAction.href(item))};
        }

        if (rowAction.RenderModal) {
            return {...base, RenderModal: rowAction.RenderModal};
        }

        if (rowAction.confirm) {
            return {
                ...base,
                modalHeader: rowAction.confirm.title,
                RenderModal: ({items: [item], closeModal}) => (
                    <RowActionModal rowAction={rowAction} item={item} closeModal={closeModal} context={context} />
                ),
            };
        }

        return {
            ...base,
            callback: ([item]) =>
                runListAction({run: () => rowAction.run(item), ids: [item.id], label: rowAction.label}, context),
        };
    });
}
