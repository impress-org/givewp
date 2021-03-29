import axios from 'axios';
import { store } from '../store';
import { getAPIRoot, isLoggedIn } from '../../../utils';
import { setAnnualReceipts, setQuerying } from '../store/actions';

export const fetchAnnualReceiptsFromAPI = () => {

	const { dispatch } = store;
	const loggedIn = isLoggedIn();

	if ( loggedIn ) {
		dispatch( setQuerying( true ) );
		axios.post( getAPIRoot() + 'give-api/v2/donor-dashboard/annual-receipts', {},
			{} )
			.then( ( response ) => response.data )
			.then( ( data ) => {
				const { receipts } = data;
				dispatch( setAnnualReceipts( receipts ) );
				dispatch( setQuerying( false ) );
			} )
			.catch( () => {
				dispatch( setQuerying( false ) );
			} );
	}
};
