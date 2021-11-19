import axios from 'axios';
import {getAPIRoot} from '../../../utils';
import {fetchSubscriptionsDataFromAPI} from '../../../tabs/recurring-donations/utils';

export const updateSubscriptionWithAPI = ({id, amount, paymentMethod}) => {
    return axios
        .post(
            getAPIRoot() + 'give-api/v2/donor-dashboard/recurring-donations/subscription/update',
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
