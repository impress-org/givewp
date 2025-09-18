import { useEntityRecord } from '@wordpress/core-data';
import { useDispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import apiFetch from '@wordpress/api-fetch';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import type { GiveSubscriptionOptions } from '@givewp/subscriptions/types';

declare const window: {
    GiveSubscriptionOptions: GiveSubscriptionOptions;
} & Window;

/**
 * @since 4.8.0
 */
export function useSubscriptionEntityRecord(subscriptionId?: number) {
    const urlParams = new URLSearchParams(window.location.search);

    const {
        record: subscription,
        hasResolved,
        isResolving,
        save,
        edit,
    }: {
        record: Subscription;
        hasResolved: boolean;
        isResolving: boolean;
        save: () => any;
        edit: (data: Subscription | Partial<Subscription>) => void;
    } = useEntityRecord('givewp', 'subscription', subscriptionId ?? urlParams.get('id'));

    return {record: subscription, hasResolved, isResolving, save, edit};
}

/**
 * @since 4.8.0
 */
export function useRefreshSubscriptionInBackground() {
    const { receiveEntityRecords, invalidateResolution } = useDispatch(coreStore);

    const refreshSubscriptionInBackground = async (subscriptionId: number) => {
        if (!subscriptionId) return;

        try {
            const latestSubscriptionData = await apiFetch({
                path: `/givewp/v3/subscriptions/${subscriptionId}`,
            });

            receiveEntityRecords('givewp', 'subscription', latestSubscriptionData, undefined, false);
        } catch (error) {
            console.error('Error refreshing subscription in background:', error);
            invalidateResolution('getEntityRecord', ['givewp', 'subscription', subscriptionId]);
        }
    };

    return refreshSubscriptionInBackground;
}

/**
 * @since 4.8.0
 */
export function getSubscriptionOptionsWindowData(): GiveSubscriptionOptions {
    return window.GiveSubscriptionOptions;
}
