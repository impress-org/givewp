import {useAsyncSelectOptions} from '@givewp/admin/hooks/useAsyncSelectOption';
import {Donor} from '@givewp/donors/admin/components/types';

/**
 *
 * Custom hook used to fetch donors
 *
 * @unreleased
 */
export default function useDonorAsyncSelect(donorId: number | null) {
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
            includeSensitiveData: 'true',
            anonymousDonors: 'include',
            onlyWithDonations: 'false',
        },
    });
}
