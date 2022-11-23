import {__, sprintf} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import ListTableApi from '@givewp/components/ListTable/api';
import tableStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {SubscriptionsRowActions} from './SubscriptionsRowActions';
import {IdBadge} from '@givewp/components/ListTable/TableCell';
import {Interweave} from 'interweave';

declare global {
    interface Window {
        GiveSubscriptions: {
            apiNonce: string;
            apiRoot: string;
            table: {columns: Array<object>};
        };
    }
}

const API = new ListTableApi(window.GiveSubscriptions);

const test = [
    {
        id: 'id',
        label: 'ID',
        sortable: true,
        visible: true,
    },
    {
        id: 'amount',
        label: 'Amount',
        sortable: true,
        visible: true,
    },
    {
        id: 'donorName',
        label: 'Donor Name',
        sortable: true,
        visible: true,
    },
    {
        id: 'donationForm',
        label: 'Donation Form',
        sortable: true,
        visible: true,
    },
    {
        id: 'billingPeriod',
        label: 'Billing Period',
        sortable: true,
        visible: true,
    },
    {
        id: 'nextPaymentDate',
        label: 'Next Payment Date',
        sortable: true,
        visible: true,
    },
    {
        id: 'status',
        label: 'Status',
        sortable: true,
        visible: true,
    },
];

//ToDo : Remove Function when GiveSubscriptionForms support columns
// Do not release : Testing Purposes only
const table = {columns: test};
window.GiveSubscriptions = {
    ...window?.GiveSubscriptions,
    table,
};
console.log(window.GiveSubscriptions);
// End Test: ----------

const filters: Array<FilterConfig> = [
    {
        name: 'search',
        type: 'search',
        inlineSize: '14rem',
        text: __('Name, Email, or Donation ID', 'give'),
        ariaLabel: __('search donations', 'give'),
    },
    {
        name: 'form',
        type: 'formselect',
        text: __('Select Form', 'give'),
        ariaLabel: __('filter donation forms by status', 'give'),
        options: window.GiveDonations.forms,
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
                <p>{__('Really delete the following donations?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((donationId, index) => (
                        <li key={donationId}>
                            <IdBadge id={donationId} />{' '}
                            <span>{sprintf(__('from %s', 'give'), <Interweave content={names[index]} />)}</span>
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
    ...(() => {
        const donationStatuses = {
            active: __('Set To Active', 'give'),
            expired: __('Set To Expired', 'give'),
            processing: __('Set To Completed', 'give'),
            cancelled: __('Set To Cancelled', 'give'),
            pending: __('Set To Pending', 'give'),
            failed: __('Set To Failing', 'give'),
            suspended: __('Set To Suspended', 'give'),
            abandoned: __('Set To Abandoned', 'give'),
        };

        return Object.entries(donationStatuses).map(([value, label]) => {
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

export default function SubscriptionsListTable() {
    return (
        <ListTablePage
            title={__('Subscriptions', 'give')}
            singleName={__('subscription', 'give')}
            pluralName={__('subscriptions', 'give')}
            rowActions={SubscriptionsRowActions}
            bulkActions={bulkActions}
            apiSettings={window.GiveSubscriptions}
            filterSettings={filters}
        >
            <button className={tableStyles.addFormButton} onClick={showLegacyDonations}>
                {__('Switch to Legacy View')}
            </button>
        </ListTablePage>
    );
}

const showLegacyDonations = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.reload();
};
