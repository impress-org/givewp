import {__, _x} from '@wordpress/i18n';
import {format} from 'date-fns';
import {useState} from 'react';
import EventFormModal from '../../EventFormModal';
import locale from '../../../../date-fns-locale';
import SectionTable from '../SectionTable';

const apiSettings = window.GiveEventTicketsDetails;

/**
 * @unreleased
 */
export default function EventSection({setUpdateErrors}) {
    const {event} = window.GiveEventTicketsDetails;
    const startDateTime = new Date(event.startDateTime.date);
    const endDateTime = new Date(event.endDateTime.date);
    const dateFormat = _x("MM/dd/yyyy 'at' h:mmaaa", 'Date format for event details page', 'give');

    const [isOpen, setOpen] = useState<boolean>(false);
    const openModal = () => setOpen(true);
    const closeModal = (response: ResponseProps | null) => {
        setOpen(false);

        if (response?.id) {
            // Update the event details
        }
    };
    const tableHeaders = {
        title: __('Event', 'give'),
        description: __('Description', 'give'),
        startDatetime: __('Start Date', 'give'),
        endDateTime: __('End Date', 'give'),
    };

    const data = [
        {
            ...event,
            startDateTime: format(startDateTime, dateFormat, {locale}),
            endDateTime: format(endDateTime, dateFormat, {locale}),
        },
    ];

    return (
        <section>
            <h2>{__('Event', 'give')}</h2>
            <SectionTable tableHeaders={tableHeaders} data={data} />
            <EventFormModal
                isOpen={isOpen}
                handleClose={closeModal}
                title={__('Edit your event', 'give')}
                apiSettings={apiSettings}
                event={data}
            />
        </section>
    );
}

interface ResponseProps {
    id: string;
    title: string;
    description: string;
    startDateTime: {
        date: string;
    };
    endDateTime: {
        date: string;
    };
}
