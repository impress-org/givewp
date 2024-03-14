import EventTicketsHeader from '../../components/EventTicketsHeader';
import EventTicketsDescription from '../../components/EventTicketsDescription';
import EventTicketsListHOC from './EventTicketsListHOC';
import {Event} from '../../components/types';

import './styles.scss';

export default function EventTicketsField({
    name,
    id,
    title,
    description,
    startDateTime,
    endDateTime,
    ticketTypes,
    ticketsLabel,
}: Event) {
    const startDateTimeObj = new Date(startDateTime);
    const endDateTimeObj = new Date(endDateTime);
    const hasEnded = endDateTimeObj < new Date();

    return (
        <div className={'givewp-event-tickets'}>
            <EventTicketsHeader title={title} startDateTime={startDateTimeObj} endDateTime={endDateTimeObj} />

            {description && <EventTicketsDescription description={description} />}

            {!hasEnded && <EventTicketsListHOC name={name} ticketTypes={ticketTypes} ticketsLabel={ticketsLabel} />}
        </div>
    );
}
