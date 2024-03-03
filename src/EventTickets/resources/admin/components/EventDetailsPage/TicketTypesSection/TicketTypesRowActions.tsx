import {useContext} from 'react';
import {ShowConfirmModalContext} from '../InnerPageListTable';
import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import ListTableApi from '@givewp/components/ListTable/api';
import {ApiSettingsProps} from '../types';

const apiSettings: ApiSettingsProps = {
    ...window.GiveEventTicketsDetails,
    table: window.GiveEventTicketsDetails.ticketTypesTable,
};
apiSettings.apiRoot += `/event/ticket-type`;
const eventTicketsApi = new ListTableApi(apiSettings);

export function TicketTypesRowActions(openEditModal) {
    return ({item, setUpdateErrors}) => {
        const showConfirmModal = useContext(ShowConfirmModalContext);

        const handleEditClick = () => {
            const {id, title, description, priceInMinorAmount: price, capacity} = item;
            openEditModal({
                id,
                title,
                description,
                price: price / 100,
                capacity,
            });
        };

        const fetchAndUpdateErrors = async (endpoint, args = {}, method) => {
            const response = await eventTicketsApi.fetchWithArgs(endpoint, args, method);
            setUpdateErrors(response);
            return response;
        };

        // Todo: Set correct endpoint
        const deleteItem = async (selected) => await fetchAndUpdateErrors('/' + item.id, {}, 'DELETE');

        const confirmDelete = (selected) => <p>{sprintf(__('Really delete ticket #%d?', 'give'), item.id)}</p>;

        const confirmModal = (event) => {
            showConfirmModal(__('Delete', 'give'), confirmDelete, deleteItem, 'danger');
        };

        return (
            <>
                <RowAction onClick={handleEditClick} displayText={__('Edit', 'give')} />
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
}
