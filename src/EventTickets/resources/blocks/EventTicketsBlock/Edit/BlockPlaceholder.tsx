import EventTicketsHeader from '../../../components/EventTicketsHeader';
import EventTicketsDescription from '../../../components/EventTicketsDescription';
import EventTicketsList from '../../../components/EventTicketsList';
import {getWindowData} from '@givewp/form-builder/common';

/**
 * @unreleased
 */
export default function BlockPlaceholder({attributes}) {
    const {events, ticketsLabel, soldOutMessage} = window.eventTicketsBlockSettings;
    const event = events.find((event) => event.id === attributes.eventId);
    const {currency} = getWindowData();
    const config = {
        currency,
    };

    if (!event || !event.tickets.length) {
        return null;
    }

    return (
        <div className={'givewp-event-tickets-block__placeholder'}>
            <div className={'givewp-event-tickets'}>
                <EventTicketsHeader title={event.title} date={event.date} />

                {event.description && <EventTicketsDescription description={event.description} />}

                <EventTicketsList
                    tickets={event.tickets}
                    ticketsLabel={ticketsLabel}
                    soldOutMessage={soldOutMessage}
                    config={config}
                />
            </div>
        </div>
    );
}
