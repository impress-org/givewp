import EventTicketsListItem from './EventTicketsListItem';
import {EventTicketsListProps} from './types';

export default function EventTicketsList({
    tickets,
    ticketsLabel,
    soldOutMessage,
    currency,
    selectedTickets = [],
    handleSelect = null,
}: EventTicketsListProps) {
    return (
        <div className={'givewp-event-tickets__tickets'}>
            <h4>{ticketsLabel}</h4>
            {tickets.map((ticket) => {
                return (
                    <EventTicketsListItem
                        ticket={ticket}
                        selectedTickets={selectedTickets[ticket.id]?.quantity ?? 0}
                        handleSelect={
                            handleSelect ? handleSelect(ticket.id, ticket.quantity, ticket.price) : () => null
                        }
                        currency={currency}
                    />
                );
            })}
        </div>
    );
}
