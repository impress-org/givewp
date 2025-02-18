import {useEntityRecord} from '@wordpress/core-data';
import {Campaign} from '@givewp/campaigns/admin/components/types';

export default function useCampaign(campaignId: number) {
    const campaignData = useEntityRecord('givewp', 'campaign', campaignId);

    return {
        campaign: {
            ...campaignData?.record as Campaign
        },
        hasResolved: campaignData?.hasResolved,
    };
}
