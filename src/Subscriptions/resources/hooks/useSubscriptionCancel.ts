import apiFetch from '@wordpress/api-fetch';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { Subscription } from '../admin/components/types';

/**
 * @since 4.8.0
 */
const isResponseSubscription = (response: unknown): response is Subscription => {
    return typeof response === 'object' && response !== null && 'id' in response;
};

/**
 * @since 4.8.0
 */
export default function useSubscriptionCancel(subscription: Subscription) {
    const [isCancelling, setIsCancelling] = useState(false);
    const [isCancelled, setIsCancelled] = useState(false);
    const dispatch = useDispatch('givewp/admin-details-page-notifications');
    const {invalidateResolution, invalidateResolutionForStore} = useDispatch('core');

    const invalidateSubscriptionCache = () => {
        invalidateResolution('getEntityRecords', ['givewp', 'subscription']);
        invalidateResolutionForStore();
    };

    const cancel = async (trash: boolean = false) => {
        setIsCancelling(true);
        const response = await apiFetch({path: `/givewp/v3/subscriptions/${subscription.id}/cancel`, method: 'POST', data: {trash}});

        if (isResponseSubscription(response) && response.status === 'cancelled') {
            setIsCancelling(false);
            setIsCancelled(true);

            invalidateSubscriptionCache();

            if (trash) {
                dispatch.addSnackbarNotice({
                    id: 'cancel-subscription',
                    content: __('Subscription cancelled and moved to trash', 'give'),
                });
            } else {
                dispatch.addSnackbarNotice({
                    id: 'cancel-subscription',
                    content: __('Subscription cancelled successfully', 'give'),
                });
            }

            return response;
        } else {
            console.error('Failed to cancel subscription', response);
            setIsCancelling(false);
            setIsCancelled(false);

            dispatch.addSnackbarNotice({
                id: 'cancel-subscription',
                content: __('Failed to cancel subscription', 'give'),
            });

            throw new Error('Failed to cancel subscription');
        }
    };

    return {
        isCancelling,
        cancel,
        isCancelled,
    };
}
