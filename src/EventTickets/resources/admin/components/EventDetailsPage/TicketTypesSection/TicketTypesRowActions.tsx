import {__, sprintf} from '@wordpress/i18n';
import RowAction from '@givewp/components/ListTable/RowAction';
import EventTicketsApi from '../../api';

const apiSettings = window.GiveEventTicketsDetails;
const eventTicketsApi = new EventTicketsApi(apiSettings);

export function TicketTypesRowActions({tickets, setTickets, openEditModal}) {
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

        const deleteItem = async (selected) => eventTicketsApi.fetchWithArgs('/ticket-type/' + ticket.id, {}, 'DELETE');

        const confirmModal = async () => {
            if (ticket.salesCount > 0) {
                alert(__('This ticket type cannot be deleted because it has donations associated with it.', 'give'));
                return;
            }

            const confirmDelete = confirm(sprintf(__('Really delete ticket #%d?', 'give'), ticket.id));

            if (confirmDelete) {
                if (await deleteItem(ticket.id)) {
                    setTickets((tickets) => tickets.filter((t) => t.id !== ticket.id));
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
