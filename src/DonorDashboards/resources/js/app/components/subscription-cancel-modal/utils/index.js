import {donorDashboardApi} from '../../../utils';
import {fetchSubscriptionsDataFromAPI} from '../../../tabs/recurring-donations/utils';
import {__} from '@wordpress/i18n';
import {store} from '../../../tabs/recurring-donations/store';
import {setError} from '../../../tabs/recurring-donations/store/actions';

export const cancelSubscriptionWithAPI = async (id) => {
    const {dispatch} = store;

    try {
        const response = await donorDashboardApi.post(
            'recurring-donations/subscription/cancel',
            {
                id: id,
            },
            {}
        );

        await fetchSubscriptionsDataFromAPI();

        return response;
    } catch (error) {
        if (error.response.status === 500) {
            dispatch(
                setError(
                    __(
                        'An error occurred while processing your request.  Please try again later, or contact support if the issue persists.',
                        'give'
                    )
                )
            );
        } else {
            dispatch(setError(error.response.data.message));
        }
        return error.response;
    }
};
