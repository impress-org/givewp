import axios from 'axios';
import { store } from '../store';
import { getAPIRoot, getAPINonce } from '../../../utils';
import { setDonations, setQuerying, setCount, setRevenue, setAverage } from '../store/actions';

export const fetchDonationsDataFromAPI = () => {
	const { dispatch } = store;

	dispatch( setQuerying( true ) );
	axios.get( getAPIRoot() + 'give-api/v2/donor-profile/donations', {
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data )
		.then( ( data ) => {
			dispatch( setDonations( data.donations ) );
			dispatch( setCount( data.count ) );
			dispatch( setRevenue( data.revenue ) );
			dispatch( setAverage( data.average ) );
			dispatch( setQuerying( false ) );
		} );
};
