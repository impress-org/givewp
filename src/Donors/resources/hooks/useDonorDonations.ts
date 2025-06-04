/**
 * @unreleased
 */
import {useEntityRecords} from '@wordpress/core-data';

/**
 * @unreleased
 */
export interface DonationResponse {
    id: number;
    formTitle: string;
    createdAt: {
        date: string;
        timezone_type: number;
        timezone: string;
    };
    status: string;
    amount: {
        value: string;
        valueInMinorUnits: string;
        currency: string;
    };
}

/**
 * @unreleased
 */
interface DonationsQueryParams {
    donorId: number;
    page?: number;
    perPage?: number;
    mode?: 'test' | 'live';
    status?: 'any' | 'publish' | 'give_subscription' | 'pending' | 'processing' | 'refunded' | 'revoked' | 'failed' | 'cancelled' | 'abandoned' | 'preapproval';
}

/**
 * @unreleased
 */
export function useDonorDonations({donorId, page = 1, perPage = 5, mode = 'live', status = 'publish'}: DonationsQueryParams) {
    const query = {
        page,
        per_page: perPage,
        mode,
        donor_id: donorId,
        status,
    };

    const {records, hasResolved, isResolving} = useEntityRecords<DonationResponse>('givewp/v3', 'donations', query);

    return {
        donations: records,
        hasResolved,
        isResolving
    };
}
