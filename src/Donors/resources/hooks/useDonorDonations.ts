/**
 * @since 4.4.0
 */
import {useEntityRecords} from '@wordpress/core-data';

/**
 * @since 4.4.0
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
 * @since 4.4.0
 */
interface DonationsQueryParams {
    donorId: number;
    page?: number;
    perPage?: number;
    mode?: 'test' | 'live';
    status?: 'any' | 'publish' | 'give_subscription' | 'pending' | 'processing' | 'refunded' | 'revoked' | 'failed' | 'cancelled' | 'abandoned' | 'preapproval';
    sort?: 'id' | 'createdAt' | 'updatedAt' | 'status' | 'amount' | 'feeAmountRecovered' | 'donorId' | 'firstName' | 'lastName';
    direction?: 'ASC' | 'DESC';
}

/**
 * @since 4.4.0
 */
export function useDonorDonations({donorId, page = 1, perPage = 5, mode = 'live', status = 'any', sort = 'createdAt', direction = 'DESC'}: DonationsQueryParams) {
    const query = {
        page,
        per_page: perPage,
        mode,
        donorId: donorId,
        status,
        sort,
        direction
    };

    const {records, hasResolved, isResolving} = useEntityRecords<DonationResponse>('givewp/v3', 'donations', query);

    return {
        donations: records,
        hasResolved,
        isResolving
    };
}
