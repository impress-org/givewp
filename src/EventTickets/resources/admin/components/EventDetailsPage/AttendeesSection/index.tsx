import {__, _x} from '@wordpress/i18n';
import styles from './AttendeesSection.module.scss';
import {GiveEventTicketsDetails} from '../types';
import {useState} from 'react';
import SectionTable from '../SectionTable';
import locale from '../../../../date-fns-locale';
import {format} from 'date-fns';

const dateFormat = _x("MM/dd/yyyy 'at' h:mmaaa", 'Date format for event details page', 'give');

/**
 * Displays a blank slate for the Attendees table.
 *
 * @since 3.6.0
 */
const BlankSlate = () => {
    const imagePath = `${window.GiveEventTicketsDetails.pluginUrl}/assets/dist/images/list-table/blank-slate-attendees-icon.svg`;
    return (
        <div className={styles.container}>
            <img src={imagePath} alt={__('No attendees yet', 'give')} />
            <h3>{__('No attendees yet', 'give')}</h3>
        </div>
    );
};

export default function AttendeesSection() {
    const {
        event: {ticketTypes, tickets},
    }: GiveEventTicketsDetails = window.GiveEventTicketsDetails;
    const [data, setData] = useState(tickets);

    const getTicketTypeById = (id: number) =>
        ticketTypes.find((type) => type.id === id)?.title || __('Unknown', 'give');

    const tableHeaders = {
        id: __('ID', 'give'),
        attendeeName: __('Name', 'give'),
        attendeeEmail: __('Email', 'give'),
        ticketType: __('Ticket Type', 'give'),
        date: __('Purchase Date', 'give'),
    };

    const formattedData = data.map((ticket) => {
        return {
            ...ticket,
            attendeeName: ticket.attendee.name,
            attendeeEmail: ticket.attendee.email,
            ticketType: getTicketTypeById(ticket.ticketTypeId),
            date: format(new Date(ticket.createdAt.date), dateFormat, {locale}),
        };
    });

    return (
        <section>
            <SectionTable tableHeaders={tableHeaders} data={formattedData} blankSlate={<BlankSlate />} />
        </section>
    );
}
