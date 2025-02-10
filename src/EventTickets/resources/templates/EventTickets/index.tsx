import EventTicketsHeader from '../../components/EventTicketsHeader';
import EventTicketsDescription from '../../components/EventTicketsDescription';
import EventTicketsListHOC from './EventTicketsListHOC';
import {Event} from '../../components/types';

import './styles.scss';

/**
 * @since 3.20.0 Hide tickets once the event has ended.
 * @since 3.6.0
 */
export default function EventTicketsField({
    name,
    title,
    description,
    startDateTime,
    endDateTime,
    ticketTypes,
}: Event) {
    const startDateTimeObj = new Date(startDateTime);
    const endDateTimeObj = new Date(endDateTime);
    const hasEnded = endDateTimeObj < new Date();

    return (
        <div className={'givewp-event-tickets'}>
            <EventTicketsHeader title={title} startDateTime={startDateTimeObj} endDateTime={endDateTimeObj} />

            {description && <EventTicketsDescription description={description} />}

            {!hasEnded && <EventTicketsListHOC name={name} ticketTypes={ticketTypes} />}
        </div>
    );
}
