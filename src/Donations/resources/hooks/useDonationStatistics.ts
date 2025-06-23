import { useState, useEffect } from 'react';
import apiFetch from '@wordpress/api-fetch';

/**
 * @unreleased
 */
export interface DonationStatistics {
    donation: {
        amount: string;
        feeAmountRecovered: string | number;
        status: string;
        date: string;
        paymentMethod: string;
        mode: string;
    };
    donor: {
        id: number;
        name: string;
        email: string;
    };
    campaign: {
        id: number;
        title: string;
    };
    receipt: {
        donationDetails: any;
        subscriptionDetails: any;
        eventTicketsDetails: any;
        additionalDetails: any;
    };
}

/**
 * @unreleased
 */
export function useDonationStatistics(
    donationId?: number,
    mode: 'live' | 'test' = 'live',
    campaignId: number = 0
) {
    const {
        statistics,
        error,
        isResolving,
        hasResolved,
    } = (function() {
        const urlParams = new URLSearchParams(window.location.search);
        const resolvedDonationId = donationId ?? Number(urlParams.get('id'));

        const [statistics, setStatistics] = useState<DonationStatistics | null>(null);
        const [error, setError] = useState<Error | null>(null);
        const [isResolving, setIsResolving] = useState<boolean>(false);
        const [hasResolved, setHasResolved] = useState<boolean>(false);

        useEffect(() => {
            if (!resolvedDonationId) return;

            setIsResolving(true);
            setHasResolved(false);
            setError(null);

            apiFetch({
                path: `/givewp/v3/donations/${resolvedDonationId}/statistics?mode=${mode}&campaignId=${campaignId}`,
                method: 'GET',
            })
                .then((data: DonationStatistics) => {
                    setStatistics(data);
                    setHasResolved(true);
                })
                .catch((err: Error) => {
                    setError(err);
                    setHasResolved(true);
                })
                .finally(() => {
                    setIsResolving(false);
                });
        }, [resolvedDonationId, mode, campaignId]);

        return { statistics, error, isResolving, hasResolved };
    })();

    return { statistics, error, isResolving, hasResolved };
} 