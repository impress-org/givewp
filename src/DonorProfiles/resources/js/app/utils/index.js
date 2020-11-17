import { addTab } from '../store/actions';

export const registerTab = ( tab ) => {
	const { dispatch } = window.giveDonorProfile.store;

	// Validate the tab object
	if ( isValidTab( tab ) === true ) {
		dispatch( addTab( tab ) );
	} else {
		return null;
	}
};

const isValidTab = ( tab ) => {
	const tabPropTypes = {
		slug: 'string',
		icon: 'string',
		label: 'string',
		content: 'function',
	};

	const isValid = Object.keys( tabPropTypes ).reduce( ( acc, key ) => {
		if ( typeof tab[ key ] !== tabPropTypes[ key ] ) {
			/* eslint-disable-next-line */
			console.error( `Error registering tab! The '${ key }' property must be a ${ tabPropTypes[ key ] }.` );
			return false;
		} else if ( acc === false ) {
			return false;
		}
		return true;
	} );

	return isValid;
};

export const getWindowData = ( value ) => {
	const data = window.giveDonorProfileData;
	return data[ value ];
};

export const getAPIRoot = () => {
	return getWindowData( 'apiRoot' );
};

export const getAPINonce = () => {
	return getWindowData( 'apiNonce' );
};
