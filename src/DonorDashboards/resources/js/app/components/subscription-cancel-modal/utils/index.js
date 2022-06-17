import {donorDashboardApi} from '../../../utils';
import {fetchSubscriptionsDataFromAPI} from '../../../tabs/recurring-donations/utils';

export const cancelSubscriptionWithAPI = (id) => {
    return donorDashboardApi
        .post(
            'recurring-donations/subscription/cancel',
            {
                id: id,
            },
            {}
        )
        .then(async (response) => {
            await fetchSubscriptionsDataFromAPI();
            return response;
        });
};
