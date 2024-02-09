import EventTicketsHeader from '../../components/EventTicketsHeader';
import EventTicketsDescription from '../../components/EventTicketsDescription';
import EventTicketsListHOC from './EventTicketsListHOC';
import {Event} from '../../components/types';

import './styles.scss';

export default function EventTicketsField({
    id,
    title,
    date,
    description,
    tickets,
    ticketsLabel,
    soldOutMessage,
}: Event) {
    return (
        <div className={'givewp-event-tickets'}>
            <EventTicketsHeader title={title} date={date} />

            {description && <EventTicketsDescription description={description} />}

            <EventTicketsListHOC
                name={name}
                tickets={tickets}
                ticketsLabel={ticketsLabel}
                soldOutMessage={soldOutMessage}
            />
        </div>
    );
}
