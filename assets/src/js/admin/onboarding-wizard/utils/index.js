/* eslint-disable no-unused-vars */
// Note: no-unused-vars rule is disabled while axios logic is not enabled

import axios from 'axios';

export const getWindowData = ( value ) => {
	const data = window.giveOnboardingWizardData;
	return data[ value ];
};

export const getAPIRoot = () => {
	return getWindowData( 'apiRoot' );
};

export const getAPINonce = () => {
	return getWindowData( 'apiNonce' );
};

export const redirectToSetupPage = () => {
	window.location.href = getWindowData( 'setupUrl' );
};

/**
 * Saves global site settings and onboarding values with the Onboarding API
 *
 * @param {string} setting Name of setting to save (examples include: 'country' or 'currency')
 * @param {any} value Value to be saved (examples include: 'USA' or 'USD')
 * @return {object} Object containing saved setting and value for confirmation
 * @since 2.8.0
 */
export const saveSettingWithOnboardingAPI = ( setting, value ) => {
	// Logic for connecting to the Onboarding API
	// An object with action: 'save' and setting: ${value} is passed to the API
	// An object of the same shape should be returned to confirm the value was stored as expected
	// Note: When the below code is actually implemented, the ${value} should be
	// stringified (using qs library or JSON stringify).

	// // Setup cancel token for request
	const CancelToken = axios.CancelToken;
	const source = CancelToken.source();

	axios.post( getAPIRoot() + 'give-api/v2/onboarding/settings', {
		cancelToken: source.token,
		setting: setting,
		value: JSON.stringify( value ),
		headers: {
			'X-WP-Nonce': getAPINonce(),
			'Content-Type': 'multipart/form-data',
		},
	} )
		.then( function( response ) {
			console.log( response ); // eslint-disable-line no-console
		} )
		.catch( function() {
			console.log( 'caught' ); // eslint-disable-line no-console
		} );

	return {
		setting,
		value,
	};
};

/**
 * Retrieves array of state/provinces from API based on country
 *
 * @param {string} country Country code of code to retrieve states/provinces of (ex: 'USD')
 * @return {array} Array of objects representing states/provinces for requested country
 * @since 2.8.0
 */
export const getStatesListWithOnboardingAPI = ( country ) => {
	// Example shape of returned data
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

	// Logic for connecting to the Onboarding API
	// An object with action: 'get_states' and country: ${country} is passed to the API
	// An array of objects with 'value' and 'label' properties for each state/province is returned

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

/**
 * Retrieves currency code from API based on country
 *
 * @param {string} country Country code of code to retrieve states/provinces of (ex: 'USD')
 * @return {string} Currency code based on requested country code
 * @since 2.8.0
 */
export const getCurrencyWithOnboardingAPI = ( country ) => {
	// Example shape of returned data
	return 'EUR';

	// Logic for connecting to the Onboarding API
	// An object with action: 'get_currency' and country: ${country} is passed to the API
	// A string with currency code for the requested country code is returned

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
