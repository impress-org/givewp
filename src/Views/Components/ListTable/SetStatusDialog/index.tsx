import {useState} from 'react';
import type {ReactNode} from 'react';
import {__} from '@wordpress/i18n';
import {Modal, SelectControl} from '@wordpress/components';
import BulkActionConfirm from '../BulkActionConfirm';
import {getItemName} from '../adapters/getItemName';
import useListActionSubmit from '../hooks/useListActionSubmit';
import type {ListTableActionDialogProps} from '../ListTablePage/types';

const label = __('Set status', 'give');

/**
 * @since TBD
 */
export type SetStatusDialogProps = ListTableActionDialogProps & {
    statuses: Array<{value: string; label: string}>;
    message: ReactNode;
    onSetStatus: (ids: string[], status: string) => Promise<unknown>;
};

/**
 * Sets one status on the chosen records. Replaces a "Set To …" bulk action per status, so the row
 * menu and the selection bar list one action.
 *
 * @since TBD
 */
export default function SetStatusDialog({
    items,
    onClose,
    statuses,
    message,
    onSetStatus,
    ...context
}: SetStatusDialogProps) {
    const [status, setStatus] = useState('');
    const {isBusy, submit} = useListActionSubmit(context);
    const ids = items.map((item) => String(item.id));

    const onConfirm = () => submit({run: () => onSetStatus(ids, status), ids, label, onDone: onClose});

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
