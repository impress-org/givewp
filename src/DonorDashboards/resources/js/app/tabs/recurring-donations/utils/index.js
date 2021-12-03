import axios from 'axios';
import {store} from '../store';
import {donorDashboardApi, isLoggedIn} from '../../../utils';
import {setSubscriptions, setQuerying, setError} from '../store/actions';

export const fetchSubscriptionsDataFromAPI = () => {
    const {dispatch} = store;
    const loggedIn = isLoggedIn();

    if (loggedIn) {
        dispatch(setQuerying(true));
        return donorDashboardApi
            .post('recurring-donations/subscriptions', {}, {})
            .then((response) => response.data)
            .then((data) => {
                dispatch(setSubscriptions(data.subscriptions));
                dispatch(setQuerying(false));

                if (data.status === 400) {
                    dispatch(setError(data.body_response.message));
                }

                return data;
            })
            .catch(() => {
                dispatch(setQuerying(false));
                return null;
            });
    }
};
