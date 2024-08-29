import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import {useSWRConfig} from 'swr';
import ListTableApi from '@givewp/components/ListTable/api';

const campaignsApi = new ListTableApi(window.GiveCampaignsListTable);

export function CampaignsRowActions({item, setUpdateErrors, parameters}) {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {mutate} = useSWRConfig();

    const donationsCount = parseInt(item?.donationsCount?.match(/^\d+/)[0], 10);

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const response = await campaignsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    const deleteItem = async (selected) => await fetchAndUpdateErrors(parameters, '', item.id, 'DELETE');

    const confirmDelete = (selected) => <p>{sprintf(__('Really delete campaign #%d?', 'give'), item.id)}</p>;

    const confirmModal = (campaign) => {
        /*if (donationsCount > 0) {
            alert(__('This campaign cannot be deleted because it has donations associated with it.', 'give'));
            return;
        }*/

        showConfirmModal(__('Delete', 'give'), confirmDelete, deleteItem, 'danger');
    };

    return (
        <>
            <RowAction
                href={`edit.php?post_type=give_forms&page=give-campaigns&id=${item.id}`}
                displayText={__('Edit', 'give')}
            />
            <RowAction
                onClick={confirmModal}
                actionId={item.id}
                displayText={__('Delete', 'give')}
                hiddenText={item.title}
                highlight
            />
        </>
    );
}
