import {__} from '@wordpress/i18n';

// Internal dependencies
import Content from './content';

import {fetchSubscriptionsDataFromAPI} from './utils';

export const registerRecurringDonationsTab = () => {
    fetchSubscriptionsDataFromAPI();

    window.giveDonorDashboard.utils.registerTab({
        label: __('Recurring Donations', 'give'),
        icon: 'sync',
        slug: 'recurring-donations',
        content: Content,
    });
};
