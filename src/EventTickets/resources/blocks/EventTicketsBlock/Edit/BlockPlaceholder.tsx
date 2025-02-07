import EventTicketsHeader from '../../../components/EventTicketsHeader';
import EventTicketsDescription from '../../../components/EventTicketsDescription';
import EventTicketsList from '../../../components/EventTicketsList';
import {getWindowData} from '@givewp/form-builder/common';

/**
 * @since 3.20.0 Hide tickets once the event has ended.
 * @since 3.6.0
 */
export default function BlockPlaceholder({attributes}) {
    const {events, ticketsLabel, soldOutMessage} = window.eventTicketsBlockSettings;
    const event = events.find((event) => event.id === attributes.eventId);
    const {currency} = getWindowData();

    if (!event) {
        return null;
    }

    const startDateTimeObj = new Date(event.startDateTime);
    const endDateTimeObj = new Date(event.endDateTime);
    const hasEnded = endDateTimeObj < new Date();

    return (
        <div className={'givewp-event-tickets-block__placeholder'}>
            <div className={'givewp-event-tickets'}>
                <EventTicketsHeader title={event.title} startDateTime={startDateTimeObj} endDateTime={endDateTimeObj} />

                {event.description && <EventTicketsDescription description={event.description} />}

                {!hasEnded && (
                    <EventTicketsList
                        ticketTypes={event.ticketTypes}
                        currency={currency}
                        currencyRate={1}
                    />
                )}
            </div>
        </div>
    );
}
