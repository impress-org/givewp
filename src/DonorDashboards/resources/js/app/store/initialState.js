import {getWindowData, getQueryParam} from '../utils';

export const initialState = {
    tabs: {},
    profile: getWindowData('profile') ? getWindowData('profile') : {},
    id: getWindowData('id'),
    countries: getWindowData('countries'),
    states: getWindowData('states'),
    fetchingStates: false,
    accentColor: getQueryParam('accent-color'),
    applicationError: null,
};
