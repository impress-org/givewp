import {__, sprintf} from '@wordpress/i18n';
import styles from './TicketTypesSection.module.scss';
import SectionTable from '../SectionTable';
import {useState} from 'react';
import TicketTypeFormModal from '../../TicketTypeFormModal';
import {TicketTypeFormContext} from '../../TicketTypeFormModal/ticketTypeFormContext';
import {TicketTypesRowActions} from './TicketTypesRowActions';
import {GiveEventTicketsDetails} from '../types';

const amountFormatter = new Intl.NumberFormat(navigator.language || navigator.languages[0], {
    style: 'currency',
    currency: window.GiveEventTicketsDetails.currencyCode,
});

/**
 * Displays a blank slate for the EventTickets table.
 *
 * @since 3.6.0
 */
const BlankSlate = ({openModal}) => {
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
 * @since 3.6.0
 */
export default function TicketTypesSection() {
    const {
        apiRoot,
        apiNonce,
        currencyCode,
        event,
        event: {ticketTypes},
    }: GiveEventTicketsDetails = window.GiveEventTicketsDetails;
    const [data, setData] = useState(ticketTypes);
    const [ticketData, setTicketData] = useState(null);

    const [isOpen, setOpen] = useState<boolean>(false);
    const openModal = (ticket = null) => {
        setTicketData(ticket);
        setOpen(true);
    };
    const closeModal = (response = null) => {
        if (response?.id) {
            setData((prevData) => {
                const filteredData = prevData.filter((ticket) => ticket.id !== response.id);
                const insertIndex = filteredData.findIndex((ticket) => response.id < ticket.id);
                const targetIndex = insertIndex === -1 ? filteredData.length : insertIndex;

                return [...filteredData.slice(0, targetIndex), response, ...filteredData.slice(targetIndex)];
            });
        }

        setOpen(false);
    };

    const tableHeaders = {
        id: __('ID', 'give'),
        title: __('Ticket', 'give'),
        count: __('No. of tickets sold', 'give'),
        price: __('Price', 'give'),
    };

    const formattedData = data.map((ticketType) => {
        return {
            ...ticketType,
            count: sprintf(__('%d of %s', 'give'), ticketType.salesCount, ticketType.capacity),
            price: ticketType.price > 0 ? amountFormatter.format(ticketType.price / 100) : __('Free', 'give'),
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
                <>
                    <SectionTable
                        tableHeaders={tableHeaders}
                        data={formattedData}
                        blankSlate={<BlankSlate openModal={openModal} />}
                        rowActions={TicketTypesRowActions({
                            tickets: data,
                            setTickets: setData,
                            openEditModal: openModal,
                        })}
                    />
                    <TicketTypeFormModal
                        apiSettings={{apiRoot, apiNonce, currencyCode}}
                        isOpen={isOpen}
                        handleClose={closeModal}
                        eventId={event?.id}
                    />
                </>
            </TicketTypeFormContext.Provider>
        </section>
    );
}

