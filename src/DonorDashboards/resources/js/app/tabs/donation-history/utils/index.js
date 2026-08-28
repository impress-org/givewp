import {store} from '../store';
import {setDonations, setQuerying, setError, setCount, setRevenue, setAverage, setCurrency} from '../store/actions';
import {donorDashboardApi, isLoggedIn} from '../../../utils';
import {store as applicationStore} from '../../../store';
import {setApplicationError} from '../../../store/actions';
import {__} from '@wordpress/i18n';

export const fetchDonationsDataFromAPI = () => {
    const {dispatch} = store;
    const applicationDispatch = applicationStore.dispatch;

    const loggedIn = isLoggedIn();

    if (loggedIn) {
        dispatch(setQuerying(true));
        donorDashboardApi
            .post('donations')
            // eslint-disable-next-line camelcase
            .then(({status, body_response}) => {
                if (status === 200) {
                    const {donations, count, revenue, average, currency} = body_response[0];

                    dispatch(setDonations(donations));
                    dispatch(setCount(count));
                    dispatch(setRevenue(revenue));
                    dispatch(setAverage(average));
                    dispatch(setCurrency(currency));
                }

                if (status === 400) {
                    dispatch(setError(body_response.message));
                }

                dispatch(setQuerying(false));
            })
            .catch((error) => {
                if (error && error.code === 'rest_cookie_invalid_nonce') {
                    applicationDispatch(
                        setApplicationError(
                            __(
                                'The site is unable to verify your access to this data. Try refreshing the page, and if the problem persists reach out to the site administrator.',
                                'give'
                            )
                        )
                    );
                }
                dispatch(setQuerying(false));
            });
    }
};
