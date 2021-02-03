import axios from 'axios';
import { store } from '../store';
import { getAPIRoot, getAPINonce } from '../../../utils';
import { setSubscriptions, setQuerying } from '../store/actions';

export const fetchSubscriptionsDataFromAPI = () => {
	const { dispatch } = store;

	dispatch( setQuerying( true ) );
	axios.post( getAPIRoot() + 'give-api/v2/donor-profile/recurring-donations/subscriptions', {},
		{
			headers: {
				'X-WP-Nonce': getAPINonce(),
			},
		} )
		.then( ( response ) => response.data )
		.then( ( data ) => {
			dispatch( setSubscriptions( data.subscriptions ) );
			dispatch( setQuerying( false ) );
		} );
};
