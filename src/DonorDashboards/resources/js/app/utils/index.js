import {addTab} from '../store/actions';
import axios from 'axios';

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

export const getAPIRoot = () => {
    return getWindowData('apiRoot');
};

export const getAPINonce = () => {
    return getWindowData('apiNonce');
};

export const donorDashboardApi = axios.create({
    baseURL: getAPIRoot() + 'give-api/v2/donor-dashboard/',
    headers: {'X-WP-Nonce': getAPINonce()},
});

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
