import axios from 'axios';
import { getAPIRoot, getAPINonce } from '../../../utils';

export const saveSubscriptionWithAPI = ( { id, amount, paymentMethod } ) => {
	return axios.post( getAPIRoot() + 'give-api/v2/donor-profile/recurring-donations/subscription/update', {
		id: id,
		amount: amount,
		payment_method: paymentMethod,
	},
	{
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => {
			return response;
		} );
};
