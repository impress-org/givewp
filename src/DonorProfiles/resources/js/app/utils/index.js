import { addTab } from '../store/actions';

export const registerTab = ( tab ) => {
	const { dispatch } = window.giveDonorProfile.store;
	dispatch( addTab( tab ) );
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
