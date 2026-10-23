import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {Modal, SelectControl} from '@wordpress/components';
import BulkActionConfirm from '@givewp/components/ListTable/BulkActionConfirm';
import {getItemName} from '@givewp/components/ListTable/adapters/getItemName';
import useListActionSubmit from '@givewp/components/ListTable/hooks/useListActionSubmit';
import type {ListTableActionDialogProps} from '@givewp/components/ListTable/ListTablePage';

/**
 * The statuses an admin can set from the list, in the order the old "Set To …" actions had.
 */
const SETTABLE_STATUSES = [
    'publish',
    'pending',
    'processing',
    'refunded',
    'revoked',
    'failed',
    'cancelled',
    'abandoned',
    'preapproval',
];

const label = __('Set status', 'give');

/**
 * @since TBD
 */
export type SetDonationStatusDialogProps = ListTableActionDialogProps & {
    statuses: Record<string, string>;
    onSetStatus: (ids: string[], status: string) => Promise<unknown>;
};

/**
 * Sets one status on the chosen donations. Replaces the nine "Set To …" bulk actions, so the row
 * menu and the selection bar list one action instead of nine.
 *
 * @since TBD
 */
export default function SetDonationStatusDialog({
    items,
    onClose,
    statuses,
    onSetStatus,
    ...context
}: SetDonationStatusDialogProps) {
    const [status, setStatus] = useState('');
    const {isBusy, submit} = useListActionSubmit(context);
    const ids = items.map((item) => String(item.id));

    const onConfirm = () => submit({run: () => onSetStatus(ids, status), ids, label, onDone: onClose});

    return (
        <Modal title={label} onRequestClose={onClose}>
            <BulkActionConfirm
                message={
                    <>
                        <p>{__('Set status for the following donations?', 'give')}</p>
                        <SelectControl
                            label={__('Status', 'give')}
                            value={status}
                            onChange={setStatus}
                            options={[
                                {value: '', label: __('Choose a status', 'give'), disabled: true},
                                ...SETTABLE_STATUSES.map((value) => ({value, label: statuses[value] ?? value})),
                            ]}
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
