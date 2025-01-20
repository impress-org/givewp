import {__} from '@wordpress/i18n';
import EventTicketsListItem from './EventTicketsListItem';
import {EventTicketsListProps} from './types';

export default function EventTicketsList({
    ticketTypes,
    currency,
    currencyRate,
    selectedTickets = [],
    handleSelect = null,
}: EventTicketsListProps) {
    if (!ticketTypes?.length) {
        return null;
    }

    return (
        <div className={'givewp-event-tickets__tickets'}>
            <h4>{__('Select tickets', 'give')}</h4>
            {ticketTypes.map((ticketType) => {
                return (
                    <EventTicketsListItem
                        ticketType={ticketType}
                        selectedTickets={selectedTickets[ticketType.id]?.quantity ?? 0}
                        handleSelect={
                            handleSelect
                                ? handleSelect(ticketType.id, ticketType.ticketsAvailable, ticketType.price)
                                : () => null
                        }
                        currency={currency}
                        currencyRate={currencyRate}
                    />
                );
            })}
        </div>
    );
}
