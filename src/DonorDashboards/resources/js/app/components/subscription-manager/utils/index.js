import {__} from '@wordpress/i18n';
import {store} from '../../../tabs/recurring-donations/store';
import {donorDashboardApi} from '../../../utils';
import {fetchSubscriptionsDataFromAPI} from '../../../tabs/recurring-donations/utils';
import {setError} from '../../../tabs/recurring-donations/store/actions';

export const updateSubscriptionWithAPI = async ({id, amount, paymentMethod}) => {
    const {dispatch} = store;

    try {
        const response = await donorDashboardApi.post(
            'recurring-donations/subscription/update',
            {
                id,
                amount,
                payment_method: paymentMethod,
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
    }
};

export const managePausingSubscriptionWithAPI = async ({id, action = 'pause', intervalInMonths = null}) => {
    const {dispatch} = store;
    try {
        const response = await donorDashboardApi.post(
            'recurring-donations/subscription/manage-pausing',
            {
                id,
                action,
                interval_in_months: intervalInMonths,
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
    }
};
