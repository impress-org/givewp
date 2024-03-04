import {__, sprintf} from '@wordpress/i18n';
import styles from './TicketTypesSection.module.scss';
import SectionTable from '../SectionTable';
import {useState} from 'react';

const amountFormatter = new Intl.NumberFormat(navigator.language || navigator.languages[0], {
    style: 'currency',
    currency: window.GiveEventTicketsDetails.currencyCode,
});

/**
 * Displays a blank slate for the EventTickets table.
 *
 * @unreleased
 */
const BlankSlate = () => {
    const imagePath = `${window.GiveEventTicketsDetails.pluginUrl}/assets/dist/images/list-table/blank-slate-event-tickets-icon.svg`;
    return (
        <div className={styles.container}>
            <img src={imagePath} alt={__('No ticket created yet', 'give')} />
            <h3>{__('No ticket created yet', 'give')}</h3>
            <p className={styles.helpMessage}>{__('Create a ticket to complete your event setup.', 'give')}</p>
            <p>
                {/*Todo: Set an onClick event to open the CreateTicketTypeModal*/}
                <a
                    href={`${window.GiveEventTicketsDetails.adminUrl}edit.php?post_type=give_forms&page=give-event-tickets&new=event`}
                    className={`button button-primary ${styles.button}`}
                >
                    {__('Create ticket', 'give')}
                </a>
            </p>
        </div>
    );
};

export default function TicketTypesSection() {
    const {
        event: {ticketTypes},
    } = window.GiveEventTicketsDetails;
    const [data, setData] = useState(ticketTypes);

    const tableHeaders = {
        id: __('ID', 'give'),
        title: __('Ticket', 'give'),
        count: __('No. of tickets sold', 'give'),
        price: __('Price', 'give'),
    };

    const formattedData = data.map((ticketType) => {
        return {
            ...ticketType,
            count: sprintf(__('%d of %d', 'give'), ticketType.salesCount, ticketType.capacity),
            price: amountFormatter.format(ticketType.price / 100),
        };
    });


    return (
        <section>
            <h2>{__('Tickets', 'give')}</h2>
            <SectionTable tableHeaders={tableHeaders} data={formattedData} blankSlate={<BlankSlate />} />
        </section>
    );
}

