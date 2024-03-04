import {useContext} from 'react';
import {ShowConfirmModalContext} from '../InnerPageListTable';
import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import ListTableApi from '@givewp/components/ListTable/api';
import {ApiSettingsProps} from '../types';

const apiSettings: ApiSettingsProps = {
    ...window.GiveEventTicketsDetails,
    table: window.GiveEventTicketsDetails.attendeesTable,
};
apiSettings.apiRoot += `/event/tickets`;
const eventTicketsApi = new ListTableApi(apiSettings);

export function AttendeesRowActions(openEditModal) {
    return ({item, setUpdateErrors}) => {
        return; // Todo: implement Attendees Row Actions
        const showConfirmModal = useContext(ShowConfirmModalContext);

        const handleEditClick = () => {
            const {id} = item;
            openEditModal({
                id,
            });
        };

        const fetchAndUpdateErrors = async (endpoint, args = {}, method) => {
            const response = await eventTicketsApi.fetchWithArgs(endpoint, args, method);
            setUpdateErrors(response);
            return response;
        };

        // Todo: Set correct endpoint
        const deleteItem = async (selected) => await fetchAndUpdateErrors('/' + item.id, {}, 'DELETE');

        const confirmDelete = (selected) => <p>{sprintf(__('Really delete attendee #%d?', 'give'), item.id)}</p>;

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
