import {useEntityRecords} from '@wordpress/core-data';
import {Campaign} from '@givewp/campaigns/admin/components/types';

type useCampaignsParams = {
    ids?: number[],
    page?: number,
    per_page?: number;
}

export default function useCampaigns({ids = [], page = 1, per_page = 30}: useCampaignsParams = {}) {
    const data = useEntityRecords('givewp', 'campaign', {ids, page, per_page});

    return {
        campaigns: data?.records as Campaign[],
        hasResolved: data?.hasResolved,
    };
}
