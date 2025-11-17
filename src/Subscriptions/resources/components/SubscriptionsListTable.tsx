import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import ListTableApi from '@givewp/components/ListTable/api';
import tableStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {SubscriptionsRowActions} from './SubscriptionsRowActions';
import {IdBadge} from '@givewp/components/ListTable/TableCell';
import {Interweave} from 'interweave';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';
import { StatConfig } from '@givewp/components/ListTable/ListTableStats/ListTableStats';
import filterByOptions from '../constants/filterByOptions';

declare global {
    interface Window {
        GiveSubscriptions: {
            apiNonce: string;
            apiRoot: string;
            table: {columns: Array<object>};
            forms: Array<{value: string; text: string}>;
            paymentMode: boolean;
            pluginUrl: string;
            subscriptionStatuses: {[statusCode: string]: string};
        };
        GiveSubscriptionOptions?: {
            currency: string;
        };
    }
}

const API = new ListTableApi(window.GiveSubscriptions);

const filters: Array<FilterConfig> = [
    {
        name: 'campaignId',
        type: 'campaignselect',
        text: __('Select Campaign', 'give'),
        ariaLabel: __('filter subscriptions by campaign', 'give'),
        options: window.GiveSubscriptions.forms,
    },
    {
        name: 'search',
        type: 'search',
        inlineSize: '14rem',
        text: __('Name, Email, or Subscription ID', 'give'),
        ariaLabel: __('search subscriptions', 'give'),
    },
    {
        name: 'toggle',
        type: 'checkbox',
        text: __('Test', 'give'),
        ariaLabel: __('View Test Subscriptions', 'give'),
    },
    {
        name: 'filterBy',
        type: 'filterby',
        groupedOptions: filterByOptions,
    }
];

const bulkActions: Array<BulkActionsConfig> = [
    {
        label: __('Delete', 'give'),
        value: 'delete',
        type: 'danger',
        isVisible: (data, parameters) => parameters?.status?.includes('trashed'),
        action: async (selected) => {
            const response = await API.fetchWithArgs('/delete', {ids: selected.join(',')}, 'DELETE');
            return response;
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Really delete the following subscriptions?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((subscriptionId, index) => (
                        <li key={subscriptionId}>
                            <IdBadge id={subscriptionId} />{' '}
                            <span>
                                {__('from ', 'give')} <Interweave content={names[index]} />
                            </span>
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
    {
        label: __('Trash', 'give'),
        value: 'trash',
        type: 'warning',
        isVisible: (data, parameters) => !parameters?.status?.includes('trashed'),
        action: async (selected) => {
            const response = await API.fetchWithArgs('/trash', {ids: selected.join(',')}, 'DELETE');
            return response;
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Are you sure you want add to trash the following subscriptions?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((subscriptionId, index) => (
                        <li key={subscriptionId}>
                            <IdBadge id={subscriptionId} />{' '}
                            <span>
                                {__('from ', 'give')} <Interweave content={names[index]} />
                            </span>
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
    {
        label: __('Restore', 'give'),
        value: 'restore',
        type: 'normal',
        isVisible: (data, parameters) => parameters?.status?.includes('trashed'),
        action: async (selected) => {
            const response = await API.fetchWithArgs('/untrash', {ids: selected.join(',')}, 'POST');
            return response;
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Are you sure you want remove from trash the following subscriptions?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((subscriptionId, index) => (
                        <li key={subscriptionId}>
                            <IdBadge id={subscriptionId} />{' '}
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
                isVisible: (data, parameters) => !parameters?.status?.includes('trashed'),
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
                        <p>{__('Set status for the following subscriptions?', 'give')}</p>
                        <ul role="document" tabIndex={0}>
                            {selected.map((subscriptionId, index) => (
                                <li key={subscriptionId}>
                                    <IdBadge id={subscriptionId} /> <span>{__('from', 'give')}</span>
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
 * Displays a blank slate for the Subscriptions table.
 * @since 2.27.0
 */
const ListTableBlankSlate = (
    <BlankSlate
        imagePath={`${window.GiveSubscriptions.pluginUrl}build/assets/dist/images/list-table/blank-slate-recurring-icon.svg`}
        description={__('No subscriptions found', 'give')}
        href={'https://docs.givewp.com/subscriptions'}
        linkText={__('Recurring Donations.', 'give')}
    />
);

/**
 * Configuration for the statistic tiles rendered above the ListTable.
 *
 * IMPORTANT: Object keys MUST MATCH the keys returned by the API's `stats` payload.
 * For example, if the API returns:
 *
 *   data.stats = {
 *     totalContributions: number;
 *     activeSubscriptions: number;
 *   }
 *
 * then this config must use those same keys: "totalContributions", "activeSubscriptions".
 * Missing or mismatched keys will result in empty/undefined values in the UI.
 *
 * @since 4.12.0
 */
const statsConfig: Record<string, StatConfig> = {
    totalContributions: {
        label: __('Total Contributions', 'give'),
        currency: window.GiveSubscriptionOptions?.currency
    },
    activeSubscriptions: { label: __('Active Subscriptions', 'give')},
};

export default function SubscriptionsListTable() {
    return (
        <ListTablePage
            title={__('Subscriptions', 'give')}
            singleName={__('subscription', 'give')}
            pluralName={__('subscriptions', 'give')}
            rowActions={SubscriptionsRowActions}
            statsConfig={statsConfig}
            bulkActions={bulkActions}
            apiSettings={window.GiveSubscriptions}
            filterSettings={filters}
            paymentMode={!!window.GiveSubscriptions.paymentMode}
            listTableBlankSlate={ListTableBlankSlate}
        >
            <button className={`button button-tertiary ${tableStyles.secondaryActionButton}`} onClick={showLegacyDonations}>
                {__('Switch to Legacy View', 'give')}
            </button>
        </ListTablePage>
    );
}

const showLegacyDonations = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.reload();
};
