import EventTicketsHeader from '../../components/EventTicketsHeader';
import EventTicketsDescription from '../../components/EventTicketsDescription';
import EventTicketsListHOC from './EventTicketsListHOC';
import {Event} from '../../components/types';

import './styles.scss';

export default function EventTicketsField({
    name,
    id,
    title,
    date,
    description,
    ticketTypes,
    ticketsLabel,
    soldOutMessage,
}: Event) {
    return (
        <div className={'givewp-event-tickets'}>
            <EventTicketsHeader title={title} date={date} />

            {description && <EventTicketsDescription description={description} />}

            <EventTicketsListHOC name={name} ticketTypes={ticketTypes} ticketsLabel={ticketsLabel} />
        </div>
    );
}
