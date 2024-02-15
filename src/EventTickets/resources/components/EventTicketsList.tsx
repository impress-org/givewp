import EventTicketsListItem from './EventTicketsListItem';
import {EventTicketsListProps} from './types';

export default function EventTicketsList({
    ticketTypes,
    ticketsLabel,
    currency,
    selectedTickets = [],
    handleSelect = null,
}: EventTicketsListProps) {
    if (!ticketTypes?.length) {
        return null;
    }

    return (
        <div className={'givewp-event-tickets__tickets'}>
            <h4>{ticketsLabel}</h4>
            {ticketTypes.map((ticketType) => {
                return (
                    <EventTicketsListItem
                        ticketType={ticketType}
                        selectedTickets={selectedTickets[ticketType.id]?.quantity ?? 0}
                        handleSelect={
                            handleSelect
                                ? handleSelect(ticketType.id, ticketType.totalTickets, ticketType.price)
                                : () => null
                        }
                        currency={currency}
                    />
                );
            })}
        </div>
    );
}
