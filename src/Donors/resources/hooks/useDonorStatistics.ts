import {useEntityRecord} from '@wordpress/core-data';

/**
 * @unreleased
 */
export interface DonorStatistics {
    donations: {
        lifetimeAmount: number;
        highestAmount: number;
        averageAmount: number;
        firstDonation: {
            amount: string;
            date: string;
        } | null;
        donationCount: number;
    };
    lastContribution: string | null;
    donorSince: string;
    donorType: string | null;
    preferredGivingType: 'recurring' | 'single';
}

/**
 * @unreleased
 */
export function useDonorStatistics(donorId: number, mode: 'test' | 'live' = 'live') {
    const {record, hasResolved, isResolving} = useEntityRecord<DonorStatistics>('givewp', 'donor', `${donorId}/statistics?mode=${mode}`);
console.log('useDonorStatistics', {donorId, mode, record, hasResolved, isResolving});
    return {
        statistics: record,
        hasResolved,
        isResolving
    };
}
