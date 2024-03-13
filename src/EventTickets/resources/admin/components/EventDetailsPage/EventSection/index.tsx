import {__, _x} from '@wordpress/i18n';
import {format} from 'date-fns';
import {useState} from 'react';
import EventFormModal from '../../EventFormModal';
import locale from '../../../../date-fns-locale';
import SectionTable from '../SectionTable';
import {EventSectionRowActions} from './EventSectionRowActions';
import styles from './EventSection.module.scss';

const dateFormat = _x("MM/dd/yyyy 'at' h:mmaaa", 'Date format for event details page', 'give');

/**
 * @since 3.6.0
 */
export default function EventSection({setUpdateErrors}) {
    const {apiRoot, apiNonce, event} = window.GiveEventTicketsDetails;
    const [data, setData] = useState(event);

    const [isOpen, setOpen] = useState<boolean>(false);
    const openModal = () => setOpen(true);
    const closeModal = (response = null) => {
        if (response?.id) {
            setData(response);
        }

        setOpen(false);
    };

    const tableHeaders = {
        title: __('Event', 'give'),
        description: __('Description', 'give'),
        startDateTime: __('Start Date', 'give'),
        endDateTime: __('End Date', 'give'),
    };

    const formattedData = [
        {
            ...data,
            startDateTime: format(new Date(data.startDateTime.date), dateFormat, {locale}),
            endDateTime: format(new Date(data.endDateTime.date), dateFormat, {locale}),
        },
    ];

    return (
        <section id={styles.eventSection}>
            <h2>{__('Event', 'give')}</h2>
            <SectionTable
                tableHeaders={tableHeaders}
                data={formattedData}
                rowActions={EventSectionRowActions({event: data, openEditModal: openModal})}
            />
            <EventFormModal
                isOpen={isOpen}
                handleClose={closeModal}
                apiSettings={{apiRoot, apiNonce}}
                title={__('Edit your event', 'give')}
                event={data}
            />
        </section>
    );
}
