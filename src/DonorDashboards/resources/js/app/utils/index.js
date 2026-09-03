import apiFetch from '@wordpress/api-fetch';
import {__} from '@wordpress/i18n';
import {addTab} from '../store/actions';

export const registerTab = (tab) => {
    const {dispatch} = window.giveDonorDashboard.store;

    // Validate the tab object
    if (isValidTab(tab) === true) {
        dispatch(addTab(tab));
    } else {
        return null;
    }
};

const isValidTab = (tab) => {
    const tabPropTypes = {
        slug: 'string',
        icon: 'string',
        label: 'string',
        content: 'function',
    };

    const isValid = Object.keys(tabPropTypes).reduce((acc, key) => {
        if (typeof tab[key] !== tabPropTypes[key]) {
            /* eslint-disable-next-line */
            console.error(`Error registering tab! The '${key}' property must be a ${tabPropTypes[key]}.`);
            return false;
        } else if (acc === false) {
            return false;
        }
        return true;
    });

    return isValid;
};

export const getWindowData = (value) => {
    const data = window.giveDonorDashboardData;
    return data[value];
};

export const getQueryParam = (param) => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
};

export const isLoggedIn = () => {
    return Number(getWindowData('id')) !== 0 ? true : false;
};

const DONOR_DASHBOARD_NAMESPACE = '/give-api/v2/donor-dashboard/';

/**
 * Posts to a Donor Dashboard REST route and resolves with the parsed response body.
 *
 * FormData is sent as-is so the browser can set its own multipart boundary. Anything
 * else is sent as JSON.
 *
 * @since 4.16.8
 *
 * @param {string} endpoint Route relative to the Donor Dashboard namespace.
 * @param {Object|FormData} data Request payload.
 * @return {Promise<Object>} Parsed response body.
 */
export const donorDashboardApi = {
    post: (endpoint, data) =>
        apiFetch({
            path: DONOR_DASHBOARD_NAMESPACE + endpoint,
            method: 'POST',
            ...(data instanceof window.FormData ? {body: data} : {data: data || {}}),
        }),
};

/**
 * Translates a rejected apiFetch request into a message safe to show a donor.
 *
 * apiFetch rejects with the parsed REST error body for a response the server actually produced,
 * and otherwise with a client-side object carrying only a code and a message - offline, or a
 * response that was not JSON because PHP failed partway through. Only the first kind describes
 * something a donor can act on, so it is the only kind whose message is shown; everything else,
 * server faults included, falls back to the generic message.
 *
 * @since 4.16.8
 *
 * @param {Object} error Rejection value from apiFetch.
 * @return {string} Message to display.
 */
export const getApiErrorMessage = (error) => {
    const status = error && error.data ? error.data.status : null;

    if (!status || status >= 500 || !error.message) {
        return __(
            'An error occurred while processing your request.  Please try again later, or contact support if the issue persists.',
            'give'
        );
    }

    return error.message;
};

/**
 * Returns string in Kebab Case (ex: kebab-case)
 *
 * @param {string} str String to be returned in Kebab Case
 * @return {string} String returned in Kebab Case
 * @since 2.8.0
 */
export const toKebabCase = (str) => {
    return str
        .replace(' / ', ' ')
        .replace(/([a-z])([A-Z])/g, '$1-$2')
        .replace(/\s+/g, '-')
        .toLowerCase();
};

/**
 * Returns a unique id in kebab case for components
 *
 * @param {string} str String to be returned as unique id
 * @return {string} String returned as unique id
 * @since 2.8.0
 */
export const toUniqueId = (str) => {
    const prefix = str ? str : 'component';
    return toKebabCase(`${prefix}-${Math.floor(Math.random() * Math.floor(1000))}`);
};
