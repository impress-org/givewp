import EventTicketsHeader from '../../components/EventTicketsHeader';
import EventTicketsDescription from '../../components/EventTicketsDescription';
import EventTicketsList from '../../components/EventTicketsList';
import {Event} from './types';

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
    const {useWatch, useCurrencyFormatter, useDonationSummary, useFormContext} = window.givewp.form.hooks;
    const {setValue} = useFormContext();
    const currency = useWatch({name: 'currency'});
    const config = {
        currency,
        useDonationSummary,
        setValue,
    };

    return (
        <div className={'givewp-event-tickets'}>
            <EventTicketsHeader title={title} date={date} />

            {description && <EventTicketsDescription description={description} />}

            <EventTicketsList
                tickets={tickets}
                ticketsLabel={ticketsLabel}
                soldOutMessage={soldOutMessage}
                config={config}
            />
        </div>
    );
}
