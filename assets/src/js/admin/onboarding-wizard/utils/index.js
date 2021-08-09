/* eslint-disable no-unused-vars */
// Note: no-unused-vars rule is disabled while axios logic is not enabled

import axios from 'axios';
import { setStateList, setFetchingStatesList } from '../app/store/actions';

export const getWindowData = ( value ) => {
	const data = window.giveOnboardingWizardData;
	return data[ value ];
};

/**
 * Returns string in Kebab Case (ex: kebab-case)
 *
 * @param {string} str String to be returned in Kebab Case
 * @return {string} String returned in Kebab Case
 * @since 2.8.0
 */
export const toKebabCase = ( str ) => {
	return str.replace( ' / ', ' ' )
		.replace( /([a-z])([A-Z])/g, '$1-$2' )
		.replace( /\s+/g, '-' )
		.toLowerCase();
};

export const getAPIRoot = () => {
	return getWindowData( 'apiRoot' );
};

export const getAPINonce = () => {
	return getWindowData( 'apiNonce' );
};

export const getCountryList = () => {
	return getWindowData( 'countries' ).map( ( country ) => {
		return {
			value: country.value,
			label: decodeHTMLEntity( country.label ),
		};
	} );
};

export const getDefaultStateList = () => {
	return getWindowData( 'states' ).map( ( state ) => {
		return {
			value: state.value,
			label: decodeHTMLEntity( state.label ),
		};
	} );
};

export const getCurrencyList = () => {
	return getWindowData( 'currencies' ).map( ( currency ) => {
		return {
			value: currency.value,
			label: decodeHTMLEntity( currency.label.admin_label ),
		};
	} );
};

export const getLocaleCurrency = ( countryCode ) => {
	const lookup = getWindowData( 'localeCurrency' );
	return lookup[ countryCode ] ?? '';
};

export const getDefaultCountry = () => {
	return getWindowData( 'countrySelected' );
};

export const getDefaultState = () => {
	return getWindowData( 'stateSelected' );
};

export const getDefaultCurrency = () => {
	return getWindowData( 'currencySelected' );
};

export const getCauseTypes = () => {
	return getWindowData( 'causeTypes' );
};

export const getFeaturesEnabledDefault = () => {
	const features = getWindowData( 'features' );
	return features.filter( ( feature ) => {
		return feature.value;
	} ).map( ( feature ) => {
		return feature.label;
	} );
};

export const getAddonsSelectedDefault = () => {
	return getWindowData( 'addons' );
};

export const decodeHTMLEntity = ( entity ) => {
	const div = document.createElement( 'div' );
	div.innerHTML = entity;
	return div.innerText;
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

	axios.post( getAPIRoot() + 'give-api/v2/onboarding/settings/' + setting, {
		value: JSON.stringify( value ),
	}, {
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} );

	return {
		setting,
		value,
	};
};

/**
 * Subscribes admin to ActiveCampaign.
 *
 * @since 2.12.1
 */
export const subscribeToNewsletter = ( configuration ) => {
	const data = {
		action: 'subscribe',
		email: getWindowData( 'adminEmail' ),
		first_name: getWindowData( 'adminFirstName' ),
		last_name: getWindowData( 'adminLastName' ),
		website_url: getWindowData( 'websiteUrl' ),
		website_name: getWindowData( 'websiteName' ),
		fundraising_type: configuration.causeType,
	};

	axios.post( 'https://connect.givewp.com/activecampaign/subscribe', data )
		.then( function( response ) {
			// Set user meta key as subscribed.
			setUserMetaSubscribed();
		} );
};

/**
 * Sets the user's meta as enabling marketing opt-in. This metakey is useful for showing or hiding notices based on whether they have opted in or not.
 *
 * @since 2.12.1
 */
export const setUserMetaSubscribed = () => {

	const currentUserId = getWindowData( 'adminUserID' );

	axios.post( getAPIRoot() + 'wp/v2/users/' + currentUserId, {
			'meta': {
				'marketing_optin': 'subscribed',
			}
		}, {
			headers: {
				'X-WP-Nonce': getAPINonce(),
			}
		}
	);
};

/**
 * Retrieves array of state/provinces from API based on country
 *
 * @param {string} country Country code of code to retrieve states/provinces of (ex: 'USD')
 * @param {requestCallback} dispatch Dispatch an action with the returned data
 * @since 2.8.0
 */
export const fetchStatesListWithOnboardingAPI = ( country, dispatch ) => {
	dispatch( setFetchingStatesList( true ) );
	axios.get( getAPIRoot() + 'give-api/v2/onboarding/location', {
		params: {
			countryCode: country,
		},
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} )
		.then( ( response ) => response.data )
		.then( ( data ) => {
			const stateList = data.states.map( ( state ) => {
				return {
					value: state.value,
					label: decodeHTMLEntity( state.label ),
				};
			} );
			dispatch( setStateList( stateList ) );
			dispatch( setFetchingStatesList( false ) );
		} );
};

/**
 * @param {requestCallback} dispatch Dispatch an action with the returned data
 * @since 2.8.0
 */
export const generateFormPreviewWithOnboardingAPI = ( dispatch ) => {
	axios.post( getAPIRoot() + 'give-api/v2/onboarding/form', {}, {
		headers: {
			'X-WP-Nonce': getAPINonce(),
		},
	} );
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

	// axios.get( getAPIRoot() + 'give-api/v2/onboarding/', {
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
