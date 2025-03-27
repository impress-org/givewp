import {useEntityRecords} from '@wordpress/core-data';
import {Campaign} from '@givewp/campaigns/admin/components/types';

type CampaignStatus = 'active' | 'draft' | 'archived';

type useCampaignsParams = {
    ids?: number[],
    page?: number,
    per_page?: number;
    status?: CampaignStatus[]
    sortBy?: string;
    orderBy?: string;
}

export default function useCampaigns({
     ids = [],
     page = 1,
     per_page = 30,
     status = ['active'],
     sortBy = 'date',
     orderBy = 'desc',
 }: useCampaignsParams = {}) {
    const data = useEntityRecords('givewp', 'campaign', {
        ids,
        page,
        per_page,
        status,
        sortBy,
        orderBy
    });

    return {
        campaigns: data?.records as Campaign[],
        //@ts-ignore
        totalItems: data.totalItems,
        //@ts-ignore
        totalPages: data.totalPages,
        hasResolved: data?.hasResolved,
    };
}
