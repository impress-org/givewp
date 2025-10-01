import {useAsyncSelectOptions} from '@givewp/admin/hooks/useAsyncSelectOption';
import {Campaign} from '@givewp/campaigns/admin/components/types';

/**
 *
 * Wrapper for custom hook used to fetch campaigns select options
 *
 * @unreleased
 */
export default function useDonorAsyncSelectOptions(campaignId: number, queryParams?: {}) {
    return useAsyncSelectOptions({
        recordId: campaignId || null,
        endpoint: '/givewp/v3/campaigns',
        entity: 'campaign',
        optionFormatter: (record: Campaign) => {
            return {
                value: record.id,
                label: record.title,
            };
        },
        queryParams: {
            sort: 'name',
            direction: 'ASC',
            ...queryParams,
        },
    });
}
