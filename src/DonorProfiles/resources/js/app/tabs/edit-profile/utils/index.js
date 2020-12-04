import axios from 'axios';
import { getAPIRoot, getAPINonce } from '../../../utils';
import { store } from '../../../store';
import { setProfile } from '../../../store/actions';

export const updateProfileWithAPI = async( {
	titlePrefix,
	firstName,
	lastName,
	primaryEmail,
	additionalEmails,
	primaryAddress,
	additionalAddresses,
	avatarFile,
	id,
} ) => {
	const { profile } = store.getState();
	let { avatarId } = profile;
	if ( avatarFile ) {
		avatarId = await uploadAvatarWithAPI( avatarFile );
	}

	const { dispatch } = store;
	axios.post( getAPIRoot() + 'give-api/v2/donor-profile/profile', {
		data: JSON.stringify( {
			titlePrefix,
			firstName,
			lastName,
			primaryEmail,
			additionalEmails,
			primaryAddress,
			additionalAddresses,
			avatarId,
		} ),
		id,
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

export const uploadAvatarWithAPI = ( file ) => {
	const formData = new window.FormData();
	formData.append( 'file', file );

	return axios.post( getAPIRoot() + 'wp/v2/media', formData, {
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data )
		.then( ( responseData ) => responseData.id );
};
