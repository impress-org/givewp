import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import ListTableApi from '@givewp/components/ListTable/api';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {IdBadge} from '@givewp/components/ListTable/TableCell';
import {Interweave} from 'interweave';
import {EventTicketsRowActions} from './EventTicketsRowActions';
import styles from './EventTicketsListTable.module.scss';
import {GiveEventTickets} from './types';
import CreateEventModal from '../CreateEventModal';
import Feedback from '../../feedback';

declare global {
    interface Window {
        GiveEventTickets: GiveEventTickets;
    }
}

const API = new ListTableApi(window.GiveEventTickets);

const filters: Array<FilterConfig> = [
    {
        name: 'search',
        type: 'search',
        inlineSize: '14rem',
        text: __('Search by keyword', 'give'),
        ariaLabel: __('search events', 'give'),
    },
];

const bulkActions: Array<BulkActionsConfig> = [
    {
        label: __('Delete', 'give'),
        value: 'delete',
        type: 'danger',
        action: async (selected) => {
            return await API.fetchWithArgs('', {ids: selected.join(',')}, 'DELETE');
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Really delete the following events?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((eventId, index) => (
                        <li key={eventId}>
                            <IdBadge id={eventId} />{' '}
                            <span>
                                {__('from ', 'give')} <Interweave content={names[index]} />
                            </span>
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
];

/**
 * Displays a blank slate for the EventTickets table.
 *
 * @since 3.6.0
 */
const ListTableBlankSlate = () => {
    const imagePath = `${window.GiveEventTickets.pluginUrl}/assets/dist/images/list-table/blank-slate-events-icon.svg`;
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

export default function EventTicketsListTable() {
    return (
        <>
            <Feedback />
            <ListTablePage
                title={__('Events', 'give')}
                singleName={__('event', 'give')}
                pluralName={__('events', 'give')}
                apiSettings={window.GiveEventTickets}
                filterSettings={filters}
                bulkActions={bulkActions}
                rowActions={EventTicketsRowActions}
                listTableBlankSlate={ListTableBlankSlate()}
            >
                <CreateEventModal />
            </ListTablePage>
        </>
    );
}
