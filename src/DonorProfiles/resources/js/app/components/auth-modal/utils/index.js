import axios from 'axios';
import { getAPIRoot, getAPINonce } from '../../../utils';

export const loginWithAPI = ( { login, password } ) => {
	return axios.post( getAPIRoot() + 'give-api/v2/donor-profile/login', {
		login,
		password,
	}, 	{
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data );
};

export const verifyEmailWithAPI = ( { email, recaptcha } ) => {
	return axios.post( getAPIRoot() + 'give-api/v2/donor-profile/verify-email', {
		email,
		'g-recaptcha-response': recaptcha,
	}, 	{
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data );
};
