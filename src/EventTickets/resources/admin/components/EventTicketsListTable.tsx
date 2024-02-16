import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import ListTableApi from '@givewp/components/ListTable/api';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {IdBadge} from '@givewp/components/ListTable/TableCell';
import {Interweave} from 'interweave';
import tableStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {EventTicketsRowActions} from './EventTicketsRowActions';

declare global {
    interface Window {
        GiveEventTickets: {
            apiNonce: string;
            apiRoot: string;
            table: {columns: Array<object>};
            adminUrl: string;
            pluginUrl: string;
        };
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
            return await API.fetchWithArgs('/delete', {ids: selected.join(',')}, 'DELETE');
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Really delete the following subscriptions?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((donationId, index) => (
                        <li key={donationId}>
                            <IdBadge id={donationId} />{' '}
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
 * @unreleased
 */
const ListTableBlankSlate = () => {
    const imagePath = `${window.GiveEventTickets.pluginUrl}/assets/dist/images/list-table/blank-slate-recurring-icon.svg`;
    return (
        <div>
            <img src={imagePath} alt={__('No event created yet', 'give')} />
            <h3>{__('No event created yet', 'give')}</h3>
        </div>
    );
};

export default function EventTicketsListTable() {
    return (
        <ListTablePage
            title={__('Events', 'give')}
            singleName={__('event', 'give')}
            pluralName={__('events', 'give')}
            apiSettings={window.GiveEventTickets}
            filterSettings={filters}
            rowActions={EventTicketsRowActions}
            listTableBlankSlate={ListTableBlankSlate()}
        >
            <a
                className={tableStyles.addFormButton}
                href={`${window.GiveEventTickets.adminUrl}edit.php?post_type=give_forms&page=give-event-tickets&action=new`}
            >
                {__('Create event', 'give')}
            </a>
        </ListTablePage>
    );
}
