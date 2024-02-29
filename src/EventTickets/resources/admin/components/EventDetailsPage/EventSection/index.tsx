import {__, _x} from '@wordpress/i18n';
import {format} from 'date-fns';
import styles from './EventSection.module.scss';
import {EventSectionRowActions} from './EventSectionRowActions';
import locale from '../../../../date-fns-locale';

/**
 * @unreleased
 */
export default function EventSection({setUpdateErrors}) {
    const {event} = window.GiveEventTicketsDetails;
    const startDateTime = new Date(event.startDateTime.date);
    const endDateTime = new Date(event.endDateTime.date);
    const dateFormat = _x("MM/dd/yyyy 'at' h:mmaaa", 'Date format for event details page', 'give');

    const tableHeaders = [
        __('Event', 'give'),
        __('Description', 'give'),
        __('Start Date', 'give'),
        __('End Date', 'give'),
    ];

    const tableContent = {
        title: event.title,
        description: event.description,
        startDateTime: format(startDateTime, dateFormat, {locale}),
        endDateTime: format(endDateTime, dateFormat, {locale}),
    };

    return (
        <section>
            <h2>{__('Event', 'give')}</h2>
            <div className={styles.tableGroup}>
                <table className={styles.table}>
                    <thead>
                        <tr>
                            {tableHeaders.map((text, key) => (
                                <th className={styles.tableColumnHeader} key={key}>
                                    {text}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        <tr className={styles.tableRow}>
                            {Object.keys(tableContent).map((key) => (
                                <td className={`${styles.tableCell} ${styles[key] ?? ''}`} key={key}>
                                    {tableContent[key]}
                                    {key === 'title' && (
                                        <div
                                            role="group"
                                            aria-label={__('Actions', 'give')}
                                            className={styles.tableRowActions}
                                        >
                                            <EventSectionRowActions
                                                item={{id: event.id, name: event.title}}
                                                setUpdateErrors={setUpdateErrors}
                                            />
                                        </div>
                                    )}
                                </td>
                            ))}
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    );
}
