import axios from 'axios';
import { getAPIRoot } from '../../../utils';
import { fetchSubscriptionsDataFromAPI } from '../../../tabs/recurring-donations/utils';

export const cancelSubscriptionWithAPI = ( id ) => {
	return axios.post( getAPIRoot() + 'give-api/v2/donor-dashboard/recurring-donations/subscription/cancel', {
		id: id,
	},
	{} )
		.then( async( response ) => {
			await fetchSubscriptionsDataFromAPI();
			return response;
		} );
};
