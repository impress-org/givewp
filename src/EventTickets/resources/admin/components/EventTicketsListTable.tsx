import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import ListTableApi from '@givewp/components/ListTable/api';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {IdBadge} from '@givewp/components/ListTable/TableCell';
import {Interweave} from 'interweave';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';

declare global {
    interface Window {
        GiveEventTickets: {
            apiNonce: string;
            apiRoot: string;
            table: {columns: Array<object>};
            paymentMode: boolean;
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
            const response = await API.fetchWithArgs('/delete', {ids: selected.join(',')}, 'DELETE');
            return response;
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
    ...(() => {
        const subscriptionStatuses = {
            active: __('Set To Active', 'give'),
            expired: __('Set To Expired', 'give'),
            completed: __('Set To Completed', 'give'),
            cancelled: __('Set To Cancelled', 'give'),
            pending: __('Set To Pending', 'give'),
            failing: __('Set To Failing', 'give'),
            suspended: __('Set To Suspended', 'give'),
            abandoned: __('Set To Abandoned', 'give'),
        };

        return Object.entries(subscriptionStatuses).map(([value, label]) => {
            return {
                label,
                value,
                action: async (selected) =>
                    await API.fetchWithArgs(
                        '/setStatus',
                        {
                            ids: selected.join(','),
                            status: value,
                        },
                        'POST'
                    ),
                confirm: (selected, names) => (
                    <>
                        <p>{__('Set status for the following donations?', 'give')}</p>
                        <ul role="document" tabIndex={0}>
                            {selected.map((donationId, index) => (
                                <li key={donationId}>
                                    <IdBadge id={donationId} /> <span>{__('from', 'give')}</span>
                                    <Interweave content={names[index]} />
                                </li>
                            ))}
                        </ul>
                    </>
                ),
            };
        });
    })(),
];

/**
 * Displays a blank slate for the EventTickets table.
 * @since 2.27.0
 */
const ListTableBlankSlate = (
    <BlankSlate
        imagePath={`${window.GiveEventTickets.pluginUrl}/assets/dist/images/list-table/blank-slate-recurring-icon.svg`}
        description={__('No events found', 'give')}
        href={'https://docs.givewp.com/subscriptions'}
        linkText={__('Recurring Donations.', 'give')}
    />
);

export default function EventTicketsListTable() {
    return (
        <ListTablePage
            title={__('Events', 'give')}
            singleName={__('event', 'give')}
            pluralName={__('events', 'give')}
            apiSettings={window.GiveEventTickets}
            filterSettings={filters}
            listTableBlankSlate={ListTableBlankSlate}
        >
        </ListTablePage>
    );
}
