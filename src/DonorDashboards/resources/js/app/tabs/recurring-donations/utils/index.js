import axios from 'axios';
import { store } from '../store';
import { getAPIRoot, isLoggedIn } from '../../../utils';
import { setSubscriptions, setQuerying } from '../store/actions';

export const fetchSubscriptionsDataFromAPI = () => {
	const { dispatch } = store;
	const loggedIn = isLoggedIn();

	if ( loggedIn ) {
		dispatch( setQuerying( true ) );
		return axios.post( getAPIRoot() + 'give-api/v2/donor-dashboard/recurring-donations/subscriptions', {},
			{} )
			.then( ( response ) => response.data )
			.then( ( data ) => {
				dispatch( setSubscriptions( data.subscriptions ) );
				dispatch( setQuerying( false ) );
				return data;
			} )
			.catch( () => {
				dispatch( setQuerying( false ) );
				return null;
			} );
		}
};
