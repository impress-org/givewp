import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import EventTicketsApi from '../../api';

const apiSettings = window.GiveEventTicketsDetails;
const eventTicketsApi = new EventTicketsApi(apiSettings);

/**
 * @unreleased
 */
export function EventSectionRowActions({event, openEditModal}) {
    return () => {
        const deleteItem = async (itemId) =>
            await eventTicketsApi.fetchWithArgs('/events/list-table', {ids: [itemId]}, 'DELETE');

        const confirmModal = async () => {
            const confirmDelete = confirm(sprintf(__('Really delete event #%d?', 'give'), event.id));

            if (confirmDelete) {
                if (await deleteItem(event.id)) {
                    window.location.href =
                        apiSettings.adminUrl + 'edit.php?post_type=give_forms&page=give-event-tickets';
                }
            }
        };

        return (
            <>
                <RowAction onClick={openEditModal} displayText={__('Edit', 'give')} />
                <RowAction
                    onClick={confirmModal}
                    actionId={event.id}
                    displayText={__('Delete', 'give')}
                    hiddenText={event.title}
                    highlight
                />
            </>
        );
    };
}
