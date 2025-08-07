import { useState, useEffect } from 'react';
import { useEntityRecords } from '@wordpress/core-data';
import { Donation } from '@givewp/donations/admin/components/types';
import apiFetch from '@wordpress/api-fetch';

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

/**
 * @unreleased
 */
export function useDonationBySubscription(subscriptionId: number, mode: 'test' | 'live') {
    const [record, setRecord] = useState(null);
    const [isResolving, setIsResolving] = useState(true);
    const [hasResolved, setHasResolved] = useState(false);

    useEffect(() => {
        setIsResolving(true);
        apiFetch({ path: `/givewp/v3/donation?subscriptionId=${subscriptionId}&mode=${mode}` })
            .then((res) => setRecord(res[0] ?? null))
            .finally(() => {
                setIsResolving(false);
                setHasResolved(true);
            });
    }, [subscriptionId, mode]);

    return { record, isResolving, hasResolved };
}
