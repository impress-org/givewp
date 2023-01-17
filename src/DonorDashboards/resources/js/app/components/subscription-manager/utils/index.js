import {store} from '../../../tabs/recurring-donations/store';
import {donorDashboardApi} from '../../../utils';
import {fetchSubscriptionsDataFromAPI} from '../../../tabs/recurring-donations/utils';
import {setError} from '../../../tabs/recurring-donations/store/actions';

export const updateSubscriptionWithAPI = ({id, amount, paymentMethod}) => {
    const {dispatch} = store;
    return donorDashboardApi
        .post(
            'recurring-donations/subscription/update',
            {
                id: id,
                amount: amount,
                payment_method: paymentMethod,
            },
            {},
        )
        .then(async (response) => {
            if (response.data.status === 400) {
                dispatch(setError(response.data.body_response.message));
                return;
            }
            await fetchSubscriptionsDataFromAPI();
            return response;
        });
};
