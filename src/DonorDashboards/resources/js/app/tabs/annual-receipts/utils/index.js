import axios from 'axios';
import {store} from '../store';
import {donorDashboardApi, isLoggedIn} from '../../../utils';
import {setAnnualReceipts, setQuerying, setError} from '../store/actions';

export const fetchAnnualReceiptsFromAPI = () => {
    const {dispatch} = store;
    const loggedIn = isLoggedIn();

    if (loggedIn) {
        dispatch(setQuerying(true));
        donorDashboardApi
            .post('annual-receipts', {}, {})
            .then((response) => response.data)
            .then((data) => {
                const {receipts} = data;
                dispatch(setAnnualReceipts(receipts));
                dispatch(setQuerying(false));

                if (data.status === 400) {
                    dispatch(setError(data.body_response.message));
                }
            })
            .catch(() => {
                dispatch(setQuerying(false));
            });
    }
};
