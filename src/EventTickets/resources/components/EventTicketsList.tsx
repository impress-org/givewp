import {_x} from '@wordpress/i18n';
import EventTicketsListItem from './EventTicketsListItem';
import {EventTicketsListProps} from './types';

export default function EventTicketsList({
    ticketTypes,
    currency,
    currencyRate,
    selectedTickets = [],
    handleSelect = () => null,
}: EventTicketsListProps) {
    if (!ticketTypes?.length) {
        return null;
    }

    return (
        <div className={'givewp-event-tickets__tickets'}>
            <h4>
                {_x('Select tickets', 'Title above the list of ticket types in the Event Tickets template', 'give')}
            </h4>
            {ticketTypes.map((ticketType) => {
                return (
                    <EventTicketsListItem
                        key={ticketType.id}
                        ticketType={ticketType}
                        selectedTickets={selectedTickets[ticketType.id]?.quantity ?? 0}
                        handleSelect={handleSelect(ticketType.id, ticketType.ticketsAvailable, ticketType.price)}
                        currency={currency}
                        currencyRate={currencyRate}
                    />
                );
            })}
        </div>
    );
}
