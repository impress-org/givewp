import {useState} from 'react';
import {__} from '@wordpress/i18n';
import type {Action} from '@wordpress/dataviews';
import BulkActionConfirm from '../BulkActionConfirm';
import type {
    BulkActionsConfig,
    ListTableItem,
    ListTableParameters,
    ListTableResponse,
    ReportOutcome,
} from '../ListTablePage/types';
import {getItemName} from './getItemName';
import {normalizeActionResult} from './normalizeActionResult';

/**
 * What a bulk action needs from the page it runs on.
 *
 * @since TBD
 */
export type BulkActionContext = {
    data: ListTableResponse | undefined;
    parameters: ListTableParameters;
    report: ReportOutcome;
    refresh: () => Promise<unknown>;
    clearSelection: () => void;
    openCustomAction: (config: BulkActionsConfig, items: ListTableItem[]) => void;
};

type StandardBulkAction = Extract<BulkActionsConfig, {action: unknown}>;

type BulkActionModalProps = {
    config: StandardBulkAction;
    items: ListTableItem[];
    closeModal?: () => void;
    context: BulkActionContext;
};

/**
 * The confirm modal of a standard bulk action: the screen's own confirm body, then Cancel /
 * Confirm. The action runs while the modal is still open, so a body with form fields can be read.
 */
function BulkActionModal({config, items, closeModal, context}: BulkActionModalProps) {
    const [isBusy, setIsBusy] = useState(false);
    const ids = items.map((item) => String(item.id));

    const onConfirm = async () => {
        setIsBusy(true);

        let outcome;

        try {
            outcome = normalizeActionResult(await config.action(ids), ids);
        } catch (error) {
            outcome = normalizeActionResult(error, ids);
        }

        closeModal?.();
        context.report(outcome, config.label);

        if (outcome.failed === 0) {
            context.clearSelection();
        }

        await context.refresh();
    };

    return (
        <BulkActionConfirm
            message={config.confirm(ids, items.map(getItemName))}
            confirmLabel={__('Confirm', 'give')}
            variant={config.type === 'danger' || config.type === 'warning' ? config.type : 'normal'}
            isBusy={isBusy}
            onConfirm={onConfirm}
            onCancel={() => closeModal?.()}
        />
    );
}

/**
 * Maps the screens' bulk actions (core and add-on, same shape) to DataViews actions.
 *
 * - `isVisible(data, parameters)` is checked on every render; a hidden action is left out.
 * - `isIdSelectable(id, data)` becomes `isEligible`, so ineligible rows are left out of the action.
 * - A `custom` action keeps its own dialog: the page opens it outside DataViews.
 *
 * @since TBD
 */
export function bulkActionsToActions(
    bulkActions: BulkActionsConfig[],
    context: BulkActionContext
): Action<ListTableItem>[] {
    return bulkActions
        .filter((config) => !config.isVisible || config.isVisible(context.data, context.parameters))
        .map((config): Action<ListTableItem> => {
            const base = {
                id: `bulk-${config.value}`,
                label: config.label,
                supportsBulk: true,
                isDestructive: config.type === 'danger',
                ...(config.isIdSelectable && {
                    isEligible: (item: ListTableItem) => config.isIdSelectable(String(item.id), context.data),
                }),
            };

            if (config.type === 'custom') {
                return {...base, callback: (items) => context.openCustomAction(config, items)};
            }

            return {
                ...base,
                modalHeader: config.label,
                RenderModal: ({items, closeModal}) => (
                    <BulkActionModal
                        config={config as StandardBulkAction}
                        items={items}
                        closeModal={closeModal}
                        context={context}
                    />
                ),
            };
        });
}
