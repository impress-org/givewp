import {donorDashboardApi, getApiErrorMessage} from '../../../utils';
import {fetchSubscriptionsDataFromAPI} from '../../../tabs/recurring-donations/utils';
import {store} from '../../../tabs/recurring-donations/store';
import {setError} from '../../../tabs/recurring-donations/store/actions';

export const cancelSubscriptionWithAPI = async (id) => {
    const {dispatch} = store;

    try {
        const response = await donorDashboardApi.post('recurring-donations/subscription/cancel', {
            id: id,
        });

        await fetchSubscriptionsDataFromAPI();

        return response;
    } catch (error) {
        dispatch(setError(getApiErrorMessage(error)));

        return error;
    }
};
