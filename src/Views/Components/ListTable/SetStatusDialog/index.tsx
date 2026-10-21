import {useState} from 'react';
import type {ReactNode} from 'react';
import {__} from '@wordpress/i18n';
import {Modal, SelectControl} from '@wordpress/components';
import type ListTableApi from '../api';
import BulkActionConfirm from '../BulkActionConfirm';
import {getItemName} from '../adapters/getItemName';
import {normalizeActionResult} from '../adapters/normalizeActionResult';
import type {ListTableActionDialogProps} from '../ListTablePage/types';

const label = __('Set status', 'give');

/**
 * @since TBD
 */
export type SetStatusDialogProps = ListTableActionDialogProps & {
    api: ListTableApi;
    statuses: Array<{value: string; label: string}>;
    message: ReactNode;
};

/**
 * Sets one status on the chosen records through the list endpoint's `/setStatus` action. Replaces a
 * "Set To …" bulk action per status, so the row menu and the selection bar list one action.
 *
 * @since TBD
 */
export default function SetStatusDialog({
    items,
    onClose,
    refresh,
    report,
    clearSelection,
    api,
    statuses,
    message,
}: SetStatusDialogProps) {
    const [status, setStatus] = useState('');
    const [isBusy, setIsBusy] = useState(false);
    const ids = items.map((item) => String(item.id));

    const onConfirm = async () => {
        setIsBusy(true);

        let outcome;

        try {
            outcome = normalizeActionResult(await api.fetchWithArgs('/setStatus', {ids: ids.join(','), status}, 'POST'), ids);
        } catch (error) {
            outcome = normalizeActionResult(error, ids);
        }

        onClose();
        report(outcome, label);

        if (outcome.failed === 0) {
            clearSelection();
        }

        await refresh();
    };

    return (
        <Modal title={label} onRequestClose={onClose}>
            <BulkActionConfirm
                message={
                    <>
                        {message}
                        <SelectControl
                            label={__('Status', 'give')}
                            value={status}
                            onChange={setStatus}
                            options={[{value: '', label: __('Choose a status', 'give'), disabled: true}, ...statuses]}
                        />
                    </>
                }
                items={items.map((item) => ({id: item.id, name: getItemName(item)}))}
                confirmLabel={label}
                isBusy={isBusy}
                canConfirm={status !== ''}
                onConfirm={onConfirm}
                onCancel={onClose}
            />
        </Modal>
    );
}
