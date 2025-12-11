import { useEntityRecords } from '@wordpress/core-data';
import { Donation } from '@givewp/donations/admin/components/types';

/**
 * @since 4.8.0
 */
export function useDonationsBySubscription(
    subscriptionId: number,
    mode: 'test' | 'live',
) {
    const queryArgs = {
        subscriptionId,
        mode,
        status: 'any',
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
