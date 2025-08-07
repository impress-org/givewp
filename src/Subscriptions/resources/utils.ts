import { useEntityRecord } from '@wordpress/core-data';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import type { GiveSubscriptionOptions } from '@givewp/subscriptions/types';

declare const window: {
    GiveSubscriptionOptions: GiveSubscriptionOptions;
} & Window;

/**
 * @unreleased
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
 * @unreleased
 */
export function getSubscriptionOptionsWindowData(): GiveSubscriptionOptions {
    return window.GiveSubscriptionOptions;
}
