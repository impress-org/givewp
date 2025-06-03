import {useEntityRecord} from '@wordpress/core-data';

/**
 * @unreleased
 */
export interface DonorStatistics {
    donations: {
        lifetimeAmount: number;
        highestAmount: number;
        averageAmount: number;
    };
    metrics: {
        firstDonation: {
            amount: string;
            date: string;
            formTitle: string;
        } | null;
        lastDonationAmount: string;
        donationCount: number;
        donorSince: string;
        donorType: string | null;
        preferredGivingType: 'recurring' | 'single';
    };
}

/**
 * @unreleased
 */
export function useDonorStatistics(donorId: number) {
    return useEntityRecord<DonorStatistics>('givewp', 'donor', `${donorId}/statistics`);
}
