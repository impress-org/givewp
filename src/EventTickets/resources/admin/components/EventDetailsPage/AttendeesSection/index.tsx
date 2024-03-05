import {__} from '@wordpress/i18n';
import styles from './AttendeesSection.module.scss';
import {GiveEventTicketsDetails} from '../types';
import {useState} from 'react';
import SectionTable from '../SectionTable';

/**
 * Displays a blank slate for the Attendees table.
 *
 * @unreleased
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
        event: {tickets},
    }: GiveEventTicketsDetails = window.GiveEventTicketsDetails;
    const [data, setData] = useState(tickets);

    const tableHeaders = {
        id: __('ID', 'give'),
        attendeeName: __('Name', 'give'),
        attendeeEmail: __('Email', 'give'),
    };

    const formattedData = data.map((ticketType) => {
        return {
            ...ticketType,
            attendeeName: ticketType.attendee.name,
            attendeeEmail: ticketType.attendee.email,
        };
    });

    return (
        <section>
            <SectionTable tableHeaders={tableHeaders} data={formattedData} blankSlate={<BlankSlate />} />
        </section>
    );
}
