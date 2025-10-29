import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import { useSWRConfig } from 'swr';

/**
 * @since 4.12.0 Revert delete action to use Donation Actions API and add trash and restore actions.
 * @since 4.6.0 Soft delete donations with Donation v3 API.
 */
export const DonationRowActions = ({item, removeRow, setUpdateErrors, parameters, listTableApi}) => {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {mutate} = useSWRConfig();

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const response = await listTableApi.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    const deleteItem = async () => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE');
    const trashItem = async () => await fetchAndUpdateErrors(parameters, '/trash', item.id, 'DELETE');
    const restoreItem = async () => await fetchAndUpdateErrors(parameters, '/untrash', item.id, 'POST');

    const confirmDelete = () => <p>{sprintf(__('Really delete donation #%d?', 'give'), item.id)}</p>;
    const confirmTrash = () => <p>{sprintf(__('Trash the following donation #%d?', 'give'), item.id)}</p>;
    const confirmRestore = () => <p>{sprintf(__('Restore the following donation #%d?', 'give'), item.id)}</p>;

    const confirmDeleteModal = () => {
        showConfirmModal(__('Delete', 'give'), confirmDelete, deleteItem, 'danger');
    };

    const confirmTrashModal = () => {
        showConfirmModal(__('Trash', 'give'), confirmTrash, trashItem, 'warning');
    };

    const confirmRestoreModal = () => {
        showConfirmModal(__('Restore', 'give'), confirmRestore, restoreItem, 'normal');
    };

    return (
        <>
            {parameters?.status?.includes('trash') ? (
                <>
                    <RowAction
                        onClick={confirmRestoreModal}
                        actionId={item.id}
                        displayText={__('Restore', 'give')}
                    />
                    <RowAction
                        onClick={confirmDeleteModal}
                        actionId={item.id}
                        displayText={__('Delete', 'give')}
                        hiddenText={item.name}
                        highlight
                    />
                </>
            ) : (
                <>
                    <RowAction
                        href={`${window.GiveDonations.adminUrl}edit.php?post_type=give_forms&page=give-payment-history&id=${item.id}`}
                        displayText={__('Edit', 'give')}
                        ariaLabel={sprintf(__('Edit donation #%d.', 'give'), item.id)}
                    />
                    <RowAction
                        onClick={confirmTrashModal}
                        actionId={item.id}
                        displayText={__('Trash', 'give')}
                        ariaLabel={sprintf(__('Move donation #%d to trash.', 'give'), item.id)}
                        hiddenText={item.name}
                        highlight
                    />
                </>
            )}
        </>
    );
};
