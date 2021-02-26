import axios from 'axios';
import { store } from '../store';
import { getAPIRoot, getAPINonce } from '../../../utils';
import { setAnnualReceipts, setQuerying } from '../store/actions';

export const fetchAnnualReceiptsFromAPI = () => {
	const { dispatch } = store;

	dispatch( setQuerying( true ) );
	axios.post( getAPIRoot() + 'give-api/v2/donor-profile/annual-receipts', {},
		{
			headers: {
				'X-WP-Nonce': getAPINonce(),
			},
		} )
		.then( ( response ) => response.data )
		.then( ( data ) => {
			const { receipts } = data;
			dispatch( setAnnualReceipts( receipts ) );
			dispatch( setQuerying( false ) );
		} );
};
