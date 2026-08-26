import { useAsyncSelectOptions } from '@givewp/admin/hooks/useAsyncSelectOption';
import { Donor } from '@givewp/donors/admin/components/types';
import { useEntityRecord } from '@wordpress/core-data';

/**
 *
 * Wrapper for custom hook used to fetch donors select options
 *
 * @since 4.11.0
 */
export default function useDonorAsyncSelectOptions(donorId: number, queryParams?: {}) {
    const { record } = useEntityRecord<string[]>('givewp', 'donor', donorId);

    return useAsyncSelectOptions({
        recordId: donorId || null,
        selectedOptionRecord: record,
        endpoint: '/givewp/v3/donors',
        optionFormatter: (record: Donor) => {
            return {
                value: record.id,
                label: `${record.name} (${record.email})`,
            };
        },
        queryParams: {
            sort: 'name',
            direction: 'ASC',
            includeSensitiveData: true,
            anonymousDonors: 'include',
            onlyWithDonations: false,
            ...queryParams,
        },
    });
}
