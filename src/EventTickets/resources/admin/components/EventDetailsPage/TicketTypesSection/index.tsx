import {__} from '@wordpress/i18n';
import styles from './TicketTypesSection.module.scss';
import {ApiSettingsProps} from '../types';
import {TicketTypesRowActions} from './TicketTypesRowActions';
import InnerPageListTable from '../InnerPageListTable';
// import CreateEventModal from '../CreateEventModal';

/**
 * Displays a blank slate for the EventTickets table.
 *
 * @unreleased
 */
const ListTableBlankSlate = () => {
    const imagePath = `${window.GiveEventTicketsDetails.pluginUrl}/assets/dist/images/list-table/blank-slate-event-tickets-icon.svg`;
    return (
        <div className={styles.container}>
            <img src={imagePath} alt={__('No ticket created yet', 'give')} />
            <h3>{__('No ticket created yet', 'give')}</h3>
            <p className={styles.helpMessage}>{__('Create a ticket to complete your event setup.', 'give')}</p>
            <p>
                {/*Todo: Set an onClick event to open the CreateTicketTypeModal*/}
                <a
                    href={`${window.GiveEventTicketsDetails.adminUrl}edit.php?post_type=give_forms&page=give-event-tickets&new=event`}
                    className={`button button-primary ${styles.button}`}
                >
                    {__('Create ticket', 'give')}
                </a>
            </p>
        </div>
    );
};

export default function TicketTypesSection() {
    const apiSettings: ApiSettingsProps = {
        ...window.GiveEventTicketsDetails,
        table: window.GiveEventTicketsDetails.ticketTypesTable,
    };
    apiSettings.apiRoot += `/event/${apiSettings.event.id}/ticket-types/list-table`;

    return (
        <section>
            <h2>{__('Tickets', 'give')}</h2>
            <InnerPageListTable
                apiSettings={apiSettings}
                singleName={__('ticket', 'give')}
                pluralName={__('tickets', 'give')}
                title={__('Tickets', 'give')}
                rowActions={TicketTypesRowActions}
                listTableBlankSlate={ListTableBlankSlate}
            />
        </section>
    );
}

