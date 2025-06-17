import {useEntityRecord} from '@wordpress/core-data';
import {Donation} from '@givewp/donations/admin/components/types';
import type {GiveDonationOptions} from '@givewp/donations/types';

declare const window: {
    GiveDonationOptions: GiveDonationOptions;
} & Window;

/**
 * @unreleased
 */
export function useDonationEntityRecord(donationId?: number) {
    const urlParams = new URLSearchParams(window.location.search);

    const {
        record,
        hasResolved,
        save,
        edit,
    }: {
        record: Donation;
        hasResolved: boolean;
        save: () => any;
        edit: (data: Donation | Partial<Donation>) => void;
    } = useEntityRecord('givewp', 'donation', donationId ?? urlParams.get('id'));

    return {record, hasResolved, save, edit};
}

/**
 * @unreleased
 */
export function getDonationOptionsWindowData(): GiveDonationOptions {
    return window.GiveDonationOptions;
}
