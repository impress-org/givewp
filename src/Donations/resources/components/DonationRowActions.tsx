import {useContext} from 'react';
import {ShowConfirmModalContext} from '@givewp/components/ListTable/ListTablePage';
import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import {useSWRConfig} from 'swr';
import ListTableApi from '@givewp/components/ListTable/api';

const API = new ListTableApi(window.GiveDonations);

export const DonationRowActions = ({item, removeRow, setUpdateErrors, parameters}) => {
    const showConfirmModal = useContext(ShowConfirmModalContext);
    const {mutate} = useSWRConfig();

    const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
        const response = await API.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        await mutate(parameters);
        return response;
    };

    const deleteItem = async (selected) => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE');

    const confirmDelete = (selected) => <p>{sprintf(__('Really delete donation #%d?', 'give'), item.id)}</p>;

    const confirmModal = (event) => {
        showConfirmModal(__('Delete', 'give'), confirmDelete, deleteItem, 'danger');
    };

    return (
        <>
            <RowAction
                href={
                    window.GiveDonations.adminUrl +
                    `edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=${item.id}`
                }
                displayText={__('Edit', 'give')}
            />
            <RowAction
                onClick={confirmModal}
                actionId={item.id}
                displayText={__('Delete', 'give')}
                hiddenText={item.name}
                highlight
            />
        </>
    );
};
