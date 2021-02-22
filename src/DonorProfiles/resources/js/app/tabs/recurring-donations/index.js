// Internal dependencies
import Content from './content';

import { fetchSubscriptionsDataFromAPI } from './utils';

export const registerRecurringDonationsTab = () => {
	fetchSubscriptionsDataFromAPI();

	window.giveDonorProfile.utils.registerTab( {
		label: 'Recurring Donations',
		icon: 'sync',
		slug: 'recurring-donations',
		content: Content,
	} );
};
