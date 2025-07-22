import {useEntityRecord} from '@wordpress/core-data';
import {Donation} from '@givewp/donations/admin/components/types';
import type {GiveDonationOptions} from '@givewp/donations/types';

declare const window: {
    GiveDonationOptions: GiveDonationOptions;
} & Window;

/**
 * @since 4.6.0
 */
export function useDonationEntityRecord(donationId?: number) {
    const urlParams = new URLSearchParams(window.location.search);

    const {
        record,
        hasResolved,
        isResolving,
        save,
        edit,
    }: {
        record: Donation;
        hasResolved: boolean;
        isResolving: boolean;
        save: () => any;
        edit: (data: Donation | Partial<Donation>) => void;
    } = useEntityRecord('givewp', 'donation', donationId ?? urlParams.get('id'));

    return {record, hasResolved, isResolving, save, edit};
}

/**
 * @since 4.6.0
 */
export function getDonationOptionsWindowData(): GiveDonationOptions {
    return window.GiveDonationOptions;
}
