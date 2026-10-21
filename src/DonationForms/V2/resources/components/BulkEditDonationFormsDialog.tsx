import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {Modal, SelectControl} from '@wordpress/components';
import ListTableApi from '@givewp/components/ListTable/api';
import BulkActionConfirm from '@givewp/components/ListTable/BulkActionConfirm';
import {normalizeActionResult} from '@givewp/components/ListTable/adapters/normalizeActionResult';
import type {ListTableActionDialogProps} from '@givewp/components/ListTable/ListTablePage';

const API = new ListTableApi(window.GiveDonationForms);

const label = __('Edit author or status', 'give');

/**
 * Changes the author or the status of the selected donation forms. Empty choices keep the current
 * value; with both empty nothing is sent.
 *
 * @since TBD Controlled fields in place of reading the old confirm dialog's markup.
 */
export default function BulkEditDonationFormsDialog({items, onClose, refresh, report, clearSelection}: ListTableActionDialogProps) {
    const [author, setAuthor] = useState('');
    const [status, setStatus] = useState('');
    const [isBusy, setIsBusy] = useState(false);
    const ids = items.map((item) => String(item.id));

    const onConfirm = async () => {
        if (!author && !status) {
            onClose();
            return;
        }

        setIsBusy(true);

        let outcome;

        try {
            outcome = normalizeActionResult(await API.fetchWithArgs('/edit', {ids: ids.join(','), author, status}, 'UPDATE'), ids);
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
                        <p>{__('Below are the donation forms to be edited.', 'give')}</p>
                        <SelectControl
                            label={__('Set form author', 'give')}
                            value={author}
                            onChange={setAuthor}
                            options={[
                                {value: '', label: __('Keep current author', 'give')},
                                ...window.GiveDonationForms.authors.map((user) => ({
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
                items={items.map((item) => ({id: item.id, name: String(item.title ?? item.id)}))}
                confirmLabel={__('Save', 'give')}
                isBusy={isBusy}
                onConfirm={onConfirm}
                onCancel={onClose}
            />
        </Modal>
    );
}
