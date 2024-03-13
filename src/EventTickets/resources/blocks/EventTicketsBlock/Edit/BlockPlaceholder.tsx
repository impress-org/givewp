import EventTicketsHeader from '../../../components/EventTicketsHeader';
import EventTicketsDescription from '../../../components/EventTicketsDescription';
import EventTicketsList from '../../../components/EventTicketsList';
import {getWindowData} from '@givewp/form-builder/common';

/**
 * @since 3.6.0
 */
export default function BlockPlaceholder({attributes}) {
    const {events, ticketsLabel, soldOutMessage} = window.eventTicketsBlockSettings;
    const event = events.find((event) => event.id === attributes.eventId);
    const {currency} = getWindowData();

    if (!event) {
        return null;
    }

    return (
        <div className={'givewp-event-tickets-block__placeholder'}>
            <div className={'givewp-event-tickets'}>
                <EventTicketsHeader title={event.title} startDateTime={new Date(event.startDateTime)} />

                {event.description && <EventTicketsDescription description={event.description} />}

                <EventTicketsList
                    ticketTypes={event.ticketTypes}
                    ticketsLabel={ticketsLabel}
                    currency={currency}
                    currencyRate={1}
                />
            </div>
        </div>
    );
}
