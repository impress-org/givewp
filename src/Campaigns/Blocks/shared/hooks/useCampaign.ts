import {useEntityRecord} from '@wordpress/core-data';
import {Campaign} from '@givewp/campaigns/admin/components/types';
import apiFetch from '@wordpress/api-fetch';
import {addQueryArgs} from '@wordpress/url';
import useSWR from 'swr';

export default function useCampaign(campaignId: number) {
    const campaignData = useEntityRecord('givewp', 'campaign', campaignId);

    return {
        campaign: {
            ...campaignData?.record as Campaign,
            forms: (params: FormsApiParams = {status: 'publish'}) => {
                const {data, isLoading}: { data: { items: [] }, isLoading: boolean } = useSWR(
                    addQueryArgs('/give-api/v2/admin/forms', {campaignId, ...params}),
                    path => apiFetch({path})
                )

                return {
                    forms: data?.items || [],
                    isLoading
                }
            }
        },
        hasResolved: campaignData?.hasResolved,
    };
}

interface FormsApiParams {
    status?: 'publish' | 'draft' | 'pending' | 'trash' | 'upgraded' | 'any'
}
