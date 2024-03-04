import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import ListTableApi from '@givewp/components/ListTable/api';

const apiSettings = window.GiveEventTicketsDetails;
const eventTicketsApi = new ListTableApi(apiSettings);

/**
 * @unreleased
 */
export function EventSectionRowActions({item, setUpdateErrors}) {
    const fetchAndUpdateErrors = async (endpoint, id, method) => {
        const response = await eventTicketsApi.fetchWithArgs(endpoint, {ids: [id]}, method);
        setUpdateErrors(response);
        return response;
    };

    const deleteItem = async (itemId) => await fetchAndUpdateErrors('/events/list-table', itemId, 'DELETE');

    const confirmModal = async () => {
        const confirmDelete = confirm(sprintf(__('Really delete event #%d?', 'give'), item.id));

        if (confirmDelete) {
            if (await deleteItem(item.id)) {
                window.location.href = window.GiveEventTicketsDetails.adminUrl + 'edit.php?post_type=give_forms&page=give-event-tickets';
            }
        }
    }

    return (
        <>
            <RowAction
                onClick={() => {alert('Edit modal will be opened')}}
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
}
