import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import ListTableApi from '@givewp/components/ListTable/api';
import {FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import SwitchToLegacyViewButton from '@givewp/components/ListTable/ListTablePage/SwitchToLegacyViewButton';
import {subscriptionsRowActions} from './SubscriptionsRowActions';
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

/**
 * @since TBD Pass the actions as data.
 */
export default function SubscriptionsListTable() {
    return (
        <ListTablePage
            title={__('Subscriptions', 'give')}
            singleName={__('subscription', 'give')}
            pluralName={__('subscriptions', 'give')}
            rowActions={subscriptionsRowActions}
            statsConfig={statsConfig}
            apiSettings={window.GiveSubscriptions}
            filterSettings={filters}
            paymentMode={!!window.GiveSubscriptions.paymentMode}
            listTableBlankSlate={ListTableBlankSlate}
        >
            <SwitchToLegacyViewButton api={API} />
        </ListTablePage>
    );
}

