import { useEntityRecord } from '@wordpress/core-data';
import { EntityRecordResolution } from '@wordpress/core-data/build-types/hooks/use-entity-record';
import {Donor} from '@givewp/donors/admin/components/types';
import type {GiveDonorOptions} from '@givewp/donors/types';

declare const window: {
    GiveDonorOptions: GiveDonorOptions;
} & Window;

/**
 * @since 4.4.0
 */
export function useDonorEntityRecord(donorId?: number): EntityRecordResolution<Donor> {
    const urlParams = new URLSearchParams(window.location.search);

    return useEntityRecord('givewp', 'donor', donorId ?? urlParams.get('id'));
}

/**
 * @since 4.4.0
 */
export function getDonorOptionsWindowData(): GiveDonorOptions {
    return window.GiveDonorOptions;
}
