import {useEntityRecord} from '@wordpress/core-data';
import {Campaign} from '@givewp/campaigns/admin/components/types';

export default function useCampaign(campaignId) {
    const data = useEntityRecord('givewp', 'campaign', campaignId);

    return {
        campaign: data?.record as Campaign,
        hasResolved: data?.hasResolved,
    };
}
