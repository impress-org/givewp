import axios from 'axios';
import { getAPIRoot, getAPINonce } from '../../../utils';
import { store } from '../../../store';
import { setProfile } from '../../../store/actions';

export const updateProfileWithAPI = ( { data, id } ) => {
	const { dispatch } = store;
	axios.post( getAPIRoot() + 'give-api/v2/donor-profile/profile', {
		data: JSON.stringify( data ),
		id: id,
	}, {
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data )
		.then( ( responseData ) => {
			dispatch( setProfile( responseData.profile ) );
		} );
};
