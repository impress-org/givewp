import axios from 'axios';
import { getAPIRoot, getAPINonce } from '../../../utils';
import { store } from '../../../store';
import { setProfile } from '../../../store/actions';

export const updateProfileWithAPI = async( {
	titlePrefix,
	firstName,
	lastName,
	company,
	primaryEmail,
	additionalEmails,
	primaryAddress,
	additionalAddresses,
	avatarFile,
	isAnonymous,
	id,
} ) => {
	/**
	 * If a new avatar file is defined, upload it and use the returned
	 * media ID to be stored as donor meta
	 */
	const { profile } = store.getState();
	let { avatarId } = profile;
	if ( avatarFile ) {
		avatarId = await uploadAvatarWithAPI( avatarFile );
	}

	/**
	 * Pass new profile data to the Profile REST endpoint
	 */
	const { dispatch } = store;
	return axios.post( getAPIRoot() + 'give-api/v2/donor-dashboard/profile', {
		data: JSON.stringify( {
			titlePrefix,
			firstName,
			lastName,
			company,
			primaryEmail,
			additionalEmails,
			primaryAddress,
			additionalAddresses,
			avatarId,
			isAnonymous,
		} ),
		id,
	}, {} )
		.then( ( response ) => response.data )
		.then( ( responseData ) => {
			/**
			 * Once updated, update the store's representation of
			 * the donor's profile data
			 */
			dispatch( setProfile( responseData.profile ) );
			return responseData;
		} );
};

export const uploadAvatarWithAPI = ( file ) => {
	// Prepare a FormData object with the file to be past to the 'media' REST endpoint
	const formData = new window.FormData();
	formData.append( 'file', file );

	// Upload the new file, and return the resolved Promise with new media ID
	return axios.post( getAPIRoot() + 'wp/v2/media', formData, {
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data )
		.then( ( responseData ) => responseData.id );
};

export const fetchStatesWithAPI = ( country ) => {
	return axios.post( getAPIRoot() + 'give-api/v2/donor-dashboard/location', {
		countryCode: country,
	}, {} )
		.then( ( response ) => response.data )
		.then( ( data ) => {
			return data.states.map( ( state ) => {
				return {
					value: state.value,
					label: decodeHTMLEntity( state.label ),
				};
			} );
		} );
};

export const decodeHTMLEntity = ( entity ) => {
	const div = document.createElement( 'div' );
	div.innerHTML = entity;
	return div.innerText;
};
