import {donorDashboardApi} from '../../../utils';

export const loginWithAPI = ({login, password}) => {
    return donorDashboardApi.post('login', {
        login,
        password,
    });
};

export const verifyEmailWithAPI = ({email, recaptcha}) => {
    return donorDashboardApi.post('verify-email', {
        email,
        'g-recaptcha-response': recaptcha,
    });
};

export const resetPasswordWithAPI = (email) => {
    return donorDashboardApi.post('reset-password', {
        email,
    });
};
