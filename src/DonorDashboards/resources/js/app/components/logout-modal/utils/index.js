import {donorDashboardApi, getQueryParam} from '../../../utils';

export const logoutWithAPI = () => {
    return donorDashboardApi.post('logout', {}).then((response) => response.data);
};

export const getCleanParentHref = () => {
    return `${window.parent.location.origin}${window.parent.location.pathname}`;
};
