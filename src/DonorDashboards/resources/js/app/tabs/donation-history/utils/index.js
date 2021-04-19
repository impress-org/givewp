import axios from 'axios';
import { store } from '../store';
import { donorDashboardApi, isLoggedIn } from '../../../utils';
import { setDonations, setQuerying, setError, setCount, setRevenue, setAverage, setCurrency } from '../store/actions';

export const fetchDonationsDataFromAPI = () => {
	const { dispatch } = store;
	const loggedIn = isLoggedIn();

	if ( loggedIn ) {
		dispatch( setQuerying( true ) );
		donorDashboardApi.post( 'donations', {},
			{} )
			.then( ( response ) => response.data )
			// eslint-disable-next-line camelcase
			.then( ( { status, body_response } ) => {
				if ( status === 200 ) {
					const { donations, count, revenue, average, currency } = body_response[ 0 ];

					dispatch( setDonations( donations ) );
					dispatch( setCount( count ) );
					dispatch( setRevenue( revenue ) );
					dispatch( setAverage( average ) );
					dispatch( setCurrency( currency ) );
				}

				if ( status === 400 ) {
					dispatch( setError( body_response.message ) );
				}

				dispatch( setQuerying( false ) );
			} )
			.catch( () => {
				dispatch( setQuerying( false ) );
			} );
	}
};
