import axios from 'axios';
import { store } from '../store';
import { getAPIRoot, getAPINonce, isLoggedIn } from '../../../utils';
import { setDonations, setQuerying, setCount, setRevenue, setAverage } from '../store/actions';

export const fetchDonationsDataFromAPI = () => {
	const { dispatch } = store;
	const loggedIn = isLoggedIn();

	if ( loggedIn ) {
		dispatch( setQuerying( true ) );
		axios.post( getAPIRoot() + 'give-api/v2/donor-profile/donations', {},
			{
				headers: {
					'X-WP-Nonce': getAPINonce(),
				},
			} )
			.then( ( response ) => response.data )
			// eslint-disable-next-line camelcase
			.then( ( { status, body_response } ) => {
				if ( status === 200 ) {
					const { donations, count, revenue, average } = body_response[ 0 ];

					dispatch( setDonations( donations ) );
					dispatch( setCount( count ) );
					dispatch( setRevenue( revenue ) );
					dispatch( setAverage( average ) );
				}

				if ( status === 400 ) {
					// eslint-disable-next-line no-console
					console.error( body_response[ 0 ].message );
				}

				dispatch( setQuerying( false ) );
			} );
	}
};
