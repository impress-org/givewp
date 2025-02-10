import {useEntityRecords} from '@wordpress/core-data';
import {Campaign} from '@givewp/campaigns/admin/components/types';

export default function useCampaigns() {
    const data = useEntityRecords('givewp', 'campaign');

    return {
        campaigns: data?.records as Campaign[],
        hasResolved: data?.hasResolved,
    };
}
