import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import {useSWRConfig} from 'swr';
import ListTableApi from '@givewp/components/ListTable/api';

const subscriptionsApi = new ListTableApi(window.GiveSubscriptions);

export function SubscriptionsRowActions({item, setUpdateErrors, parameters}) {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {mutate} = useSWRConfig();

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const response = await subscriptionsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    const deleteItem = async () => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE');
    const trashItem = async () => await fetchAndUpdateErrors(parameters, '/trash', item.id, 'DELETE');
    const restoreItem = async () => await fetchAndUpdateErrors(parameters, '/untrash', item.id, 'POST');

    const confirmDelete = () => <p>{sprintf(__('Really delete subscription #%d?', 'give'), item.id)}</p>;
    const confirmTrash = () => <p>{sprintf(__('Trash the following subscription #%d?', 'give'), item.id)}</p>;
    const confirmRestore = () => <p>{sprintf(__('Restore the following subscription #%d?', 'give'), item.id)}</p>;

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
            {parameters?.status?.includes('trashed') ? (
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
                        href={`edit.php?post_type=give_forms&page=give-subscriptions&id=${item.id}`}
                        displayText={__('Edit', 'give')}
                    />
                    <RowAction
                        onClick={confirmTrashModal}
                        actionId={item.id}
                        displayText={__('Trash', 'give')}
                        hiddenText={item.name}
                        highlight
                    />
                </>
            )}
        </>
    );
}
