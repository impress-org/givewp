import {useAsyncSelectOptions} from '@givewp/admin/hooks/useAsyncSelectOption';
import {Donor} from '@givewp/donors/admin/components/types';
import {UseDonorAsyncSelectReturn} from '@givewp/admin/fields/AssociatedDonor/types';

/**
 *
 * Wrapper for custom hook used to fetch donors select options
 *
 * @unreleased
 */
export default function useDonorAsyncSelectOptions(donorId: number, queryParams?: {}): UseDonorAsyncSelectReturn {
    return useAsyncSelectOptions({
        recordId: donorId || null,
        endpoint: '/givewp/v3/donors',
        entity: 'donor',
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
