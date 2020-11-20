import axios from 'axios';
import { getAPIRoot, getAPINonce } from '../../../utils';
import { store } from '../../../store';
import { setProfile } from '../../../store/actions';

export const updateProfileWithAPI = ( profile ) => {
	const { dispatch } = store;

	axios.get( getAPIRoot() + 'give-api/v2/donor-profile/profile', {
		profile: JSON.stringify( profile ),
	}, {
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data )
		.then( ( data ) => {
			dispatch( setProfile( data.profile ) );
		} );
};
