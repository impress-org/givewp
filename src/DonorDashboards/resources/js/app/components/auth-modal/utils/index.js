import axios from 'axios';
import {getAPIRoot} from '../../../utils';

export const loginWithAPI = ({login, password}) => {
    return axios
        .post(
            getAPIRoot() + 'give-api/v2/donor-dashboard/login',
            {
                login,
                password,
            },
            {}
        )
        .then((response) => response.data);
};

export const verifyEmailWithAPI = ({email, recaptcha}) => {
    return axios
        .post(
            getAPIRoot() + 'give-api/v2/donor-dashboard/verify-email',
            {
                email,
                'g-recaptcha-response': recaptcha,
            },
            {}
        )
        .then((response) => response.data);
};
