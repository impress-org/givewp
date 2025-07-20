import { __ } from '@wordpress/i18n';
import OverviewTab from './Overview';
import DonationsTab from './Donations';
import RecordsTab from './Records';
import { Tab } from '@givewp/components/AdminDetailsPage/types';
import { getSubscriptionOptionsWindowData } from '@givewp/subscriptions/utils';

const { adminUrl } = getSubscriptionOptionsWindowData();
const urlParams = new URLSearchParams(window.location.search);
const subscriptionId = urlParams.get('id');

/**
 * @unreleased
 */
const subscriptionDetailsTabs: Tab[] = [
    {
        id: 'overview',
        title: __('Overview', 'give'),
        content: OverviewTab,
    },
    {
        id: 'donations',
        title: __('Donations', 'give'),
        // TODO: Define if using the link or content
        // link: `${adminUrl}edit.php?post_type=give_forms&page=give-payment-history&subscription_id=${subscriptionId}`,
        content: DonationsTab,
    },
    {
        id: 'records',
        title: __('Records', 'give'),
        content: RecordsTab,
    },
];

export default subscriptionDetailsTabs;
