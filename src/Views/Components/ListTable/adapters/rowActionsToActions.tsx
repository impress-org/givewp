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
    clearSelection: () => void;
};

/**
 * Runs an action, reports what it did and reloads the list. A selection that fully succeeded clears.
 */
async function runRowAction(rowAction: ListTableRowAction, items: ListTableItem[], context: RowActionContext) {
    const ids = items.map((item) => item.id);
    let outcome;

    try {
        outcome = normalizeActionResult(await rowAction.run(items), ids);
    } catch (error) {
        outcome = normalizeActionResult(error, ids);
    }

    context.report(outcome, rowAction.label);

    if (outcome.failed === 0 && !outcome.notice) {
        context.clearSelection();
    }

    await context.refresh();
}

type RowActionModalProps = {
    rowAction: ListTableRowAction;
    items: ListTableItem[];
    closeModal?: () => void;
    context: RowActionContext;
};

function RowActionModal({rowAction, items, closeModal, context}: RowActionModalProps) {
    const [isBusy, setIsBusy] = useState(false);

    const onConfirm = async () => {
        setIsBusy(true);
        closeModal?.();
        await runRowAction(rowAction, items, context);
    };

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
            ...(rowAction.supportsBulk && {supportsBulk: true}),
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
                RenderModal: ({items, closeModal}) => (
                    <RowActionModal rowAction={rowAction} items={items} closeModal={closeModal} context={context} />
                ),
            };
        }

        return {...base, callback: (items) => runRowAction(rowAction, items, context)};
    });
}
