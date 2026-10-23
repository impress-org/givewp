import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {CheckboxControl, Modal} from '@wordpress/components';
import ListTableApi from '@givewp/components/ListTable/api';
import BulkActionConfirm from '@givewp/components/ListTable/BulkActionConfirm';
import {getItemName} from '@givewp/components/ListTable/adapters/getItemName';
import useListActionSubmit from '@givewp/components/ListTable/hooks/useListActionSubmit';
import type {ListTableActionDialogProps, ListTableRowAction} from '@givewp/components/ListTable/ListTablePage';
import './style.scss';

const donorsApi = new ListTableApi(window.GiveDonors);

const deleteLabel = __('Delete', 'give');

/**
 * @since TBD
 */
type DeleteDonorsDialogProps = ListTableActionDialogProps & {
    onDelete: (ids: string[], deleteDonationsAndRecords: boolean) => Promise<unknown>;
};

/**
 * Deletes the chosen donors, and their donations and records unless the admin unticks that.
 *
 * @since TBD Controlled checkbox in place of reading the old confirm dialog's markup.
 */
function DeleteDonorsDialog({items, onClose, onDelete, ...context}: DeleteDonorsDialogProps) {
    const [deleteDonationsAndRecords, setDeleteDonationsAndRecords] = useState(true);
    const {isBusy, submit} = useListActionSubmit(context);
    const ids = items.map((item) => String(item.id));

    const onConfirm = () =>
        submit({run: () => onDelete(ids, deleteDonationsAndRecords), ids, label: deleteLabel, onDone: onClose});

    return (
        <Modal title={__('Delete donors', 'give')} onRequestClose={onClose}>
            <BulkActionConfirm
                message={
                    <>
                        <p>{__('Are you sure you want to delete the following donors?', 'give')}</p>
                        <CheckboxControl
                            label={__('Delete all associated donations and records', 'give')}
                            checked={deleteDonationsAndRecords}
                            onChange={setDeleteDonationsAndRecords}
                        />
                    </>
                }
                items={items.map((item) => ({id: item.id, name: getItemName(item)}))}
                confirmLabel={deleteLabel}
                variant="danger"
                isBusy={isBusy}
                onConfirm={onConfirm}
                onCancel={onClose}
            />
        </Modal>
    );
}

/**
 * Donor actions: "Edit" inline, "Delete" in the row menu and the selection bar.
 *
 * @since TBD Declare the actions as data for the list engine; Delete serves rows and selections.
 */
export const donorsRowActions: ListTableRowAction[] = [
    {
        id: 'edit',
        label: __('Edit', 'give'),
        isPrimary: true,
        href: (item) =>
            `${window.GiveDonors.adminUrl}edit.php?post_type=give_forms&page=give-donors&view=overview&id=${item.id}`,
    },
    {
        id: 'delete',
        label: deleteLabel,
        isDestructive: true,
        supportsBulk: true,
        Dialog: (props) => (
            <DeleteDonorsDialog
                {...props}
                onDelete={(ids, deleteDonationsAndRecords) =>
                    donorsApi.fetchWithArgs('/delete', {ids: ids.join(','), deleteDonationsAndRecords}, 'DELETE')
                }
            />
        ),
    },
];
