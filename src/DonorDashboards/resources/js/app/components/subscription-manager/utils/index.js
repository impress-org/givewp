import {donorDashboardApi} from '../../../utils';
import {fetchSubscriptionsDataFromAPI} from '../../../tabs/recurring-donations/utils';

export const updateSubscriptionWithAPI = ({id, amount, paymentMethod}) => {
    return donorDashboardApi
        .post(
            'recurring-donations/subscription/update',
            {
                id: id,
                amount: amount,
                payment_method: paymentMethod,
            },
            {}
        )
        .then(async (response) => {
            await fetchSubscriptionsDataFromAPI();
            return response;
        });
};
