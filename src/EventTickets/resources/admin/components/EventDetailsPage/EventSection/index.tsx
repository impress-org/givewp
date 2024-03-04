import {__, _x} from '@wordpress/i18n';
import {format} from 'date-fns';
import locale from '../../../../date-fns-locale';
import SectionTable from '../SectionTable';

/**
 * @unreleased
 */
export default function EventSection({setUpdateErrors}) {
    const {event} = window.GiveEventTicketsDetails;
    const startDateTime = new Date(event.startDateTime.date);
    const endDateTime = new Date(event.endDateTime.date);
    const dateFormat = _x("MM/dd/yyyy 'at' h:mmaaa", 'Date format for event details page', 'give');

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
        </section>
    );
}
