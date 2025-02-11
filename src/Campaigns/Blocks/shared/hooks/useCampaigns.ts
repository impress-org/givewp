import {useEntityRecords} from '@wordpress/core-data';
import {Campaign} from '@givewp/campaigns/admin/components/types';

type useCampaignsParams = {
    ids?: number[],
    page?: number,
    per_page?: number;
    status?: 'active' | 'draft' | 'archived';
    orderBy?: 'date' | 'amount' | 'donations' | 'donors';
}

export default function useCampaigns({
     ids = [],
     page = 1,
     per_page = 30,
     status = 'active',
     orderBy = 'date'
 }: useCampaignsParams = {}) {
    const data = useEntityRecords('givewp', 'campaign', {ids, page, per_page, status, orderBy});

    return {
        campaigns: data?.records as Campaign[],
        //@ts-ignore
        totalItems: data.totalItems,
        //@ts-ignore
        totalPages: data.totalPages,
        hasResolved: data?.hasResolved,
    };
}
