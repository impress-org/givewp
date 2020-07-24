//import axios from 'axios';

export const getAPIRoot = () => {
	return 'givewp.local/';
};

export const getWindowData = ( value ) => {
	const data = window.giveOnboardingWizardData;
	return data[ value ];
};

export const getAPINonce = () => {
	return 'mock-nonce';
};

export const redirectToSetupPage = () => {
	window.location.href = getWindowData( 'setupUrl' );
};

export const saveSettingWithOnboardingAPI = ( setting, value ) => {
	return {
		setting,
		value,
	};

	// // Setup cancel token for request
	// const CancelToken = axios.CancelToken;
	// const source = CancelToken.source();

	// axios.get( getAPIRoot() + 'give-api/v2/onboarding/', {
	// 	cancelToken: source.token,
	// 	params: {
	// 		action: 'save',
	// 		setting: value,
	// 	},
	// 	headers: {
	// 		'X-WP-Nonce': getAPINonce(),
	// 	},
	// } )
	// 	.then( function( response ) {
	// 		// Do something on success
	// 	} )
	// 	.catch( function() {
	// 		// Do something on error
	// 	} );
};

export const getStatesListWithOnboardingAPI = () => {
	return [
		{
			value: 'FL',
			label: 'Florida',
		},
		{
			value: 'RI',
			label: 'Rhode Island',
		},
	];

	// // Setup cancel token for request
	// const CancelToken = axios.CancelToken;
	// const source = CancelToken.source();

	// axios.get( getAPIRoot() + 'give-api/v2/onboarding/', {
	// 	cancelToken: source.token,
	// 	params: {
	// 		action: 'get_states',
	// 		country,
	// 	},
	// 	headers: {
	// 		'X-WP-Nonce': getAPINonce(),
	// 	},
	// } )
	// 	.then( function( response ) {
	// 		// Return states list on success
	// 	} )
	// 	.catch( function() {
	// 		// Do something on error
	// 	} );
};

export const getCurrencyWithOnboardingAPI = () => {
	return 'EUR';

	// // Setup cancel token for request
	// const CancelToken = axios.CancelToken;
	// const source = CancelToken.source();

	// axios.get( getAPIRoot() + 'give-api/v2/onboarding/', {
	// 	cancelToken: source.token,
	// 	params: {
	// 		action: 'get_currency',
	// 		country,
	// 	},
	// 	headers: {
	// 		'X-WP-Nonce': getAPINonce(),
	// 	},
	// } )
	// 	.then( function( response ) {
	// 		// Do something on success
	// 	} )
	// 	.catch( function() {
	// 		// Do something on error
	// 	} );
};
