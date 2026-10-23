import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {Modal, SelectControl} from '@wordpress/components';
import BulkActionConfirm from '@givewp/components/ListTable/BulkActionConfirm';
import {getItemName} from '@givewp/components/ListTable/adapters/getItemName';
import useListActionSubmit from '@givewp/components/ListTable/hooks/useListActionSubmit';
import type {ListTableActionDialogProps} from '@givewp/components/ListTable/ListTablePage';

const label = __('Edit author or status', 'give');

/**
 * @since TBD
 */
export type BulkEditDonationFormsDialogProps = ListTableActionDialogProps & {
    authors: Array<{id: number | string; name: string}>;
    /** Saves the new author and status; an empty value keeps the current one. */
    onSave: (ids: string[], changes: {author: string; status: string}) => Promise<unknown>;
};

/**
 * Changes the author or the status of the selected donation forms. Empty choices keep the current
 * value; with both empty nothing is sent.
 *
 * @since TBD Controlled fields in place of reading the old confirm dialog's markup.
 */
export default function BulkEditDonationFormsDialog({
    items,
    onClose,
    authors,
    onSave,
    ...context
}: BulkEditDonationFormsDialogProps) {
    const [author, setAuthor] = useState('');
    const [status, setStatus] = useState('');
    const {isBusy, submit} = useListActionSubmit(context);
    const ids = items.map((item) => String(item.id));

    const onConfirm = () => {
        if (!author && !status) {
            onClose();
            return;
        }

        return submit({run: () => onSave(ids, {author, status}), ids, label, onDone: onClose});
    };

    return (
        <Modal title={label} onRequestClose={onClose}>
            <BulkActionConfirm
                message={
                    <>
                        <p>{__('Below are the donation forms to be edited.', 'give')}</p>
                        <SelectControl
                            label={__('Set form author', 'give')}
                            value={author}
                            onChange={setAuthor}
                            options={[
                                {value: '', label: __('Keep current author', 'give')},
                                ...authors.map((user) => ({
                                    value: String(user.id),
                                    label: user.name,
                                })),
                            ]}
                        />
                        <SelectControl
                            label={__('Set form status', 'give')}
                            value={status}
                            onChange={setStatus}
                            options={[
                                {value: '', label: __('Keep current status', 'give')},
                                {value: 'publish', label: __('Published', 'give')},
                                {value: 'private', label: __('Private', 'give')},
                                {value: 'pending', label: __('Pending Review', 'give')},
                                {value: 'draft', label: __('Draft', 'give')},
                            ]}
                        />
                    </>
                }
                items={items.map((item) => ({id: item.id, name: getItemName(item)}))}
                confirmLabel={__('Save', 'give')}
                isBusy={isBusy}
                onConfirm={onConfirm}
                onCancel={onClose}
            />
        </Modal>
    );
}
