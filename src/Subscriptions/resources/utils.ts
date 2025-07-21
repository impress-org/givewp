import { useEntityRecord } from '@wordpress/core-data';
import { Subscription } from '@givewp/subscriptions/admin/components/types';
import type { GiveSubscriptionOptions } from '@givewp/subscriptions/types';
import { EntityRecordResolution } from '@wordpress/core-data/build-types/hooks/use-entity-record';

declare const window: {
    GiveSubscriptionOptions: GiveSubscriptionOptions;
} & Window;

/**
 * @unreleased
 */
export function useSubscriptionEntityRecord(subscriptionId?: number): EntityRecordResolution<Subscription> {
    const urlParams = new URLSearchParams(window.location.search);

    return useEntityRecord('givewp', 'subscription', subscriptionId ?? urlParams.get('id'));
}

/**
 * @unreleased
 */
export function getSubscriptionOptionsWindowData(): GiveSubscriptionOptions {
    return window.GiveSubscriptionOptions;
}
