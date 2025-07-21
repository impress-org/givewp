import { useEntityRecord } from '@wordpress/core-data';
import { EntityRecordResolution } from '@wordpress/core-data/build-types/hooks/use-entity-record';
import {Donation} from '@givewp/donations/admin/components/types';
import type {GiveDonationOptions} from '@givewp/donations/types';

declare const window: {
    GiveDonationOptions: GiveDonationOptions;
} & Window;

/**
 * @unreleased
 */
export function useDonationEntityRecord(donationId?: number): EntityRecordResolution<Donation> {
    const urlParams = new URLSearchParams(window.location.search);

    return useEntityRecord('givewp', 'donation', donationId ?? urlParams.get('id'));
}

/**
 * @unreleased
 */
export function getDonationOptionsWindowData(): GiveDonationOptions {
    return window.GiveDonationOptions;
}
