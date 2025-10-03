import { useAsyncSelectOptions } from "@givewp/admin/hooks/useAsyncSelectOption";
import { Campaign } from "@givewp/campaigns/admin/components/types";
import { useEntityRecord } from "@wordpress/core-data";

export default function useCampaignAsyncSelectOptions(campaignId: number, queryParams?: {}) {
    const { record } = useEntityRecord<string[]>('givewp', 'campaign', campaignId);

    return useAsyncSelectOptions({
        recordId: campaignId || null,
        selectedOptionRecord: record,
        endpoint: '/givewp/v3/campaigns',
        optionFormatter: (record: Campaign) => {
            return {
                value: record.id,
                label: record.title,
                record,
            };
        },
        queryParams: {
            sort: 'name',
            direction: 'ASC',
            ...queryParams,
        },
    });
}
