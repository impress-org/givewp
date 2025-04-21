import {__} from "@wordpress/i18n";
import useSWR from "swr";
import {addQueryArgs} from "@wordpress/url";
import apiFetch from "@wordpress/api-fetch";
import useCampaigns from "../../../../../../Campaigns/Blocks/shared/hooks/useCampaigns";

/**
 * @unreleased
 */
export default function useCampaignOptions(attributes): {
    campaignOptions: any;
    campaignForms: any;
    hasResolved: boolean;
} {
    const {campaigns, hasResolved} = useCampaigns({status: ['active']});

    const campaignOptions = (() => {
        if (!hasResolved) {
            return [{label: __('Loading...', 'give'), value: ''}];
        }

        if (campaigns.length) {
            const campaignOptions = campaigns.map((campaign) => ({
                label: `${campaign.title} ${campaign.status === 'draft' ? `(${__('Draft', 'give')})` : ''}`.trim(),
                value: campaign.id,
            }));

            return [{label: __('Select a campaign', 'give'), value: ''}, ...campaignOptions];
        }

        return [{label: __('No campaigns found.', 'give'), value: ''}];
    })();

    const campaignForms = (() => {
        const {data, isLoading}: { data: { items: [] }, isLoading: boolean } = useSWR(
            addQueryArgs('/give-api/v2/admin/forms', {campaignId: attributes.campaignId, status: 'publish'}),
            path => apiFetch({path})
        )

        if (isLoading) {
            return [{label: __('Loading...', 'give'), value: ''}]
        }

        const options = data?.items?.map((form: { name: string; id: string }) => ({
            label: form.name,
            value: form.id
        })) ?? [];


        return [
            {label: __('Select form', 'give'), value: ''},
            ...options
        ];
    })();

    return {
        hasResolved,
        campaignOptions,
        campaignForms
    };
}
