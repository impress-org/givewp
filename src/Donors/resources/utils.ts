import {useEntityRecord} from '@wordpress/core-data';
import {Donor} from '@givewp/donors/admin/components/types';
import type {GiveDonorOptions} from '@givewp/donors/types';

declare const window: {
    GiveDonorOptions: GiveDonorOptions;
} & Window;

/**
 * @since 4.4.0
 */
export function useDonorEntityRecord(donorId?: number) {
    const urlParams = new URLSearchParams(window.location.search);

    const {
        record,
        hasResolved,
        save,
        edit,
    }: {
        record: Donor;
        hasResolved: boolean;
        save: () => any;
        edit: (data: Donor | Partial<Donor>) => void;
    } = useEntityRecord('givewp', 'donor', donorId ?? urlParams.get('id'));

    return {record, hasResolved, save, edit};
}

/**
 * @since 4.4.0
 */
export function getDonorOptionsWindowData(): GiveDonorOptions {
    return window.GiveDonorOptions;
}
