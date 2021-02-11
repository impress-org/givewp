import axios from 'axios';
import { getAPIRoot, getAPINonce } from '../../../utils';
import { fetchSubscriptionsDataFromAPI } from '../../../tabs/recurring-donations/utils';

export const cancelSubscriptionWithAPI = ( id ) => {
	return axios.post( getAPIRoot() + 'give-api/v2/donor-profile/recurring-donations/subscription/cancel', {
		id: id,
	},
	{
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( async( response ) => {
			await fetchSubscriptionsDataFromAPI();
			return response;
		} );
};
