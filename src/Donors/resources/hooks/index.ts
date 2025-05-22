import {useEntityRecord, useEntityRecords} from '@wordpress/core-data';
import {Donor, DonorsListTableParams} from '@givewp/src/Donors/resources/types';

/**
 * @unreleased
 */
export function useDonor(donorId: number) {
    const {
        record: donor,
        hasResolved,
        save,
        edit,
    }: {
        record: Donor;
        hasResolved: boolean;
        save: () => any;
        edit: (data: Donor | Partial<Donor>) => void;
    } = useEntityRecord('givewp', 'donor', donorId);

    return {donor, hasResolved, save, edit};
}


/**
 * @unreleased
 */
export default function useDonors({
    ids = [],
    page = 1,
    per_page = 30,
    sort = 'id',
    direction = 'DESC',
    onlyWithDonations = true,
    includeSensitiveData = false,
    anonymousDonors = 'exclude',
    campaignId = null
}: DonorsListTableParams = {}) {
    const data = useEntityRecords('givewp', 'donor', {
        ids,
        page,
        per_page,
        sort,
        direction,
        onlyWithDonations,
        includeSensitiveData,
        anonymousDonors,
        campaignId
    });

    return {
        campaigns: data?.records as Donor[],
        //@ts-ignore
        totalItems: data.totalItems,
        //@ts-ignore
        totalPages: data.totalPages,
        hasResolved: data?.hasResolved,
    };
}
