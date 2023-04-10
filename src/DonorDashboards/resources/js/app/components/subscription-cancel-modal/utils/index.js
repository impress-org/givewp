import {donorDashboardApi} from '../../../utils';
import {fetchSubscriptionsDataFromAPI} from '../../../tabs/recurring-donations/utils';

export const cancelSubscriptionWithAPI = async (id) => {
    try {
        const response = await donorDashboardApi.post(
            'recurring-donations/subscription/cancel',
            {
                id: id,
            },
            {}
        );

        await fetchSubscriptionsDataFromAPI();

        return await response;
    } catch (error) {
        return error.response;
    }
};
