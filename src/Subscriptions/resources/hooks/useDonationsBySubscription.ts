import { useState, useEffect } from 'react';
import { useEntityRecords } from '@wordpress/core-data';
import apiFetch from '@wordpress/api-fetch';
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
    };

    const entityResult = useEntityRecords('givewp', 'donation', queryArgs);

    return {
        records: entityResult.records as Donation[] | null,
        record: entityResult.records?.[0] as Donation | null,
        hasResolved: entityResult.hasResolved,
        isResolving: entityResult.isResolving,
    };
}