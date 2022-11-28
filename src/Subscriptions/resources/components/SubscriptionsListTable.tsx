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
            forms: Array<{value: string; text: string}>;
        };
    }
}

const API = new ListTableApi(window.GiveSubscriptions);

const filters: Array<FilterConfig> = [
    {
        name: 'search',
        type: 'search',
        inlineSize: '14rem',
        text: __('Name, Email, or  ID', 'give'),
        ariaLabel: __('search donations', 'give'),
    },
    {
        name: 'form',
        type: 'formselect',
        text: __('Select Form', 'give'),
        ariaLabel: __('filter donation forms by status', 'give'),
        options: window.GiveSubscriptions.forms,
    },
    {
        name: 'toggle',
        type: 'checkbox',
        text: __('Test', 'give'),
        ariaLabel: __('View Test Subscriptions', 'give'),
    },
];

const bulkActions: Array<BulkActionsConfig> = [
    ...(() => {
        const subscriptionStatuses = {
            active: __('Set To Active', 'give'),
            expired: __('Set To Expired', 'give'),
            processing: __('Set To Completed', 'give'),
            cancelled: __('Set To Cancelled', 'give'),
            pending: __('Set To Pending', 'give'),
            failed: __('Set To Failing', 'give'),
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
