import axios from 'axios';
import { getAPIRoot, getAPINonce } from '../../../utils';

export const logoutWithAPI = () => {
	return axios.post( getAPIRoot() + 'give-api/v2/donor-profile/logout', {
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data );
};
