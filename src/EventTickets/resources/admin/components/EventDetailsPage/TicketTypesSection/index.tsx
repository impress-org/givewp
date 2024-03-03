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
                <a onClick={() => openModal()} className={`button button-primary ${styles.button}`}>
                    {__('Create ticket', 'give')}
                </a>
            </p>
        </div>
    );
};

/**
 * @unreleased
 */
export default function TicketTypesSection() {
    const {
        event: {ticketTypes},
    } = window.GiveEventTicketsDetails;
    const [data, setData] = useState(ticketTypes);
    const [isOpen, setOpen] = useState<boolean>(false);

    const openModal = (ticket: Ticket | null) => {
        setTicketData(ticket);
        setOpen(true);
    };
    const closeModal = (response = null) => {
        setOpen(false);
    };
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
            <div className={styles.sectionHeader}>
                <h2>{__('Tickets', 'give')}</h2>
                <a className={`button button-primary ${styles.createTicketButton}`} onClick={() => openModal()}>
                    {__('Add Ticket', 'give')}
                </a>
            </div>
            <TicketTypeFormContext.Provider value={{ticketData, setTicketData}}>
            <SectionTable tableHeaders={tableHeaders} data={formattedData} blankSlate={<BlankSlate />} />
                <TicketTypeFormModal apiSettings={apiSettings} isOpen={isOpen} handleClose={closeModal} />
            </TicketTypeFormContext.Provider>
        </section>
    );
}

