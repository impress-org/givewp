import { useEntityRecords } from '@wordpress/core-data';
import { Donation } from '@givewp/donations/admin/components/types';

/**
 * @unreleased
 */
export function useDonationsBySubscription(
    subscriptionId: number,
    mode: 'test' | 'live',
) {
    const queryArgs = {
        subscriptionId,
        mode,
        status: 'publish',
        sort: 'createdAt',
        direction: 'DESC'
    };

    const {
        records,
        hasResolved,
        isResolving,
    }: {
        records: Donation[] | null;
        hasResolved: boolean;
        isResolving: boolean;
    } = useEntityRecords('givewp', 'donation', queryArgs);

    return {
        records,
        hasResolved,
        isResolving,
    };
}