import {useState} from 'react';
import type {Action} from '@wordpress/dataviews';
import BulkActionConfirm from '../BulkActionConfirm';
import type {ListTableItem, ListTableRowAction, ReportOutcome} from '../ListTablePage/types';
import {getItemName} from './getItemName';
import {normalizeActionResult} from './normalizeActionResult';

/**
 * What a row action needs from the page it runs on.
 *
 * @since TBD
 */
export type RowActionContext = {
    report: ReportOutcome;
    refresh: () => Promise<unknown>;
};

/**
 * Runs a row action, reports what it did and reloads the list.
 */
async function runRowAction(rowAction: ListTableRowAction, item: ListTableItem, context: RowActionContext) {
    const ids = [item.id];
    let outcome;

    try {
        outcome = normalizeActionResult(await rowAction.run(item), ids);
    } catch (error) {
        outcome = normalizeActionResult(error, ids);
    }

    context.report(outcome, rowAction.label);
    await context.refresh();
}

type RowActionModalProps = {
    rowAction: ListTableRowAction;
    item: ListTableItem;
    closeModal?: () => void;
    context: RowActionContext;
};

function RowActionModal({rowAction, item, closeModal, context}: RowActionModalProps) {
    const [isBusy, setIsBusy] = useState(false);

    const onConfirm = async () => {
        setIsBusy(true);
        closeModal?.();
        await runRowAction(rowAction, item, context);
    };

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

        return {...base, callback: ([item]) => runRowAction(rowAction, item, context)};
    });
}
