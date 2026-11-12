import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import {eventTicketsRowActions} from './EventTicketsRowActions';
import styles from './EventTicketsListTable.module.scss';
import {GiveEventTickets} from './types';
import CreateEventModal from '../CreateEventModal';
import Feedback from '../../feedback';
import listTableScreen from './listTableScreen';
import {eventTicketFilters} from './filters';

declare global {
    interface Window {
        GiveEventTickets: GiveEventTickets;
    }
}

/**
 * Displays a blank slate for the EventTickets table.
 *
 * @since 3.6.0
 */
const ListTableBlankSlate = () => {
    const imagePath = `${window.GiveEventTickets.pluginUrl}build/assets/dist/images/list-table/blank-slate-events-icon.svg`;
    return (
        <div className={styles.container}>
            <img src={imagePath} alt={__('No event created yet', 'give')} />
            <h3>{__('No event created yet', 'give')}</h3>
            <p className={styles.helpMessage}>{__('Don’t worry, let’s help you setup your first event.', 'give')}</p>
            <p>
                <a
                    href={`${window.GiveEventTickets.adminUrl}edit.php?post_type=give_forms&page=give-event-tickets&new=event`}
                    className={`button button-primary ${styles.button}`}
                >
                    {__('Create event', 'give')}
                </a>
            </p>
        </div>
    );
};

/**
 * EventTicketsListTable
 *
 * @since TBD Pass the filter declaration in place of the filter settings.
 * @since TBD Pass the actions as data and the screen config; Delete serves both the row menu and the selection bar.
 * @since 3.20.0 Add wrapper class for the EventTicketsListTable
 * @since 3.6.0
 */
export default function EventTicketsListTable() {
    return (
        <div className={styles.listTable}>
            <Feedback />
            <ListTablePage
                title={__('Events', 'give')}
                singleName={__('event', 'give')}
                pluralName={__('events', 'give')}
                apiSettings={window.GiveEventTickets}
                filters={eventTicketFilters}
                rowActions={eventTicketsRowActions}
                listTableBlankSlate={ListTableBlankSlate()}
                screen={listTableScreen}
            >
                <CreateEventModal />
            </ListTablePage>
        </div>
    );
}
