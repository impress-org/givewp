import axios from 'axios';
import { getAPIRoot, getAPINonce } from '../../../utils';
import { store } from '../../../store';
import { setProfile } from '../../../store/actions';

export const updateProfileWithAPI = ( { data, id } ) => {
	const { dispatch } = store;
	axios.post( getAPIRoot() + 'give-api/v2/donor-profile/profile', {
		data: JSON.stringify( data ),
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

export const updateAvatarWithAPI = ( { file, id } ) => {
	const formData = new window.FormData();
	formData.append( 'file', file );

	axios.post( getAPIRoot() + 'wp/v2/media', formData, {
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data )
		.then( ( responseData ) => {
			updateProfileWithAPI( {
				data: {
					avatarId: responseData.id,
				},
				id,
			} );
		} );
};
