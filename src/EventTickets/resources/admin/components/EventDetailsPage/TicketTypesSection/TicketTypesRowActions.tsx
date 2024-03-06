import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import EventTicketsApi from '../../api';

const apiSettings = window.GiveEventTicketsDetails;
const eventTicketsApi = new EventTicketsApi(apiSettings);

export function TicketTypesRowActions({tickets, openEditModal}) {
    return (row) => {
        const ticket = tickets.find((ticket) => ticket.id === row.id);

        const handleEditClick = () => {
            const {id, title, description, price, capacity} = ticket;
            openEditModal({
                id,
                title,
                description,
                price: price / 100,
                capacity,
            });
        };

        const deleteItem = async (selected) =>
            eventTicketsApi.fetchWithArgs('/event/ticket-type/' + ticket.id, {}, 'DELETE');

        const confirmModal = async () => {
            const confirmDelete = confirm(sprintf(__('Really delete ticket #%d?', 'give'), ticket.id));

            if (confirmDelete) {
                if (await deleteItem(ticket.id)) {
                    // Refresh data
                }
            }
        };

        return (
            <>
                <RowAction onClick={handleEditClick} displayText={__('Edit', 'give')} />
                <RowAction
                    onClick={confirmModal}
                    actionId={ticket.id}
                    displayText={__('Delete', 'give')}
                    hiddenText={ticket.title}
                    highlight
                />
            </>
        );
    };
}
