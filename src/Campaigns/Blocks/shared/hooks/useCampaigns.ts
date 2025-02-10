import {useEntityRecords} from '@wordpress/core-data';
import {Campaign} from '@givewp/campaigns/admin/components/types';

type useCampaignsParams = {
    ids?: number[],
    page?: number,
    per_page?: number;
    status?: 'active' | 'draft' | 'archived';
}

export default function useCampaigns({ids = [], page = 1, per_page = 30, status = 'active'}: useCampaignsParams = {}) {
    const data = useEntityRecords('givewp', 'campaign', {ids, page, per_page, status});

    return {
        campaigns: data?.records as Campaign[],
        hasResolved: data?.hasResolved,
    };
}
