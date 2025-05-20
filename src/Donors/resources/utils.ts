import {useEntityRecord} from '@wordpress/core-data';
import {Donor} from '@givewp/donors/admin/components/types';
import type {GiveDonorOptions} from '@givewp/donors/types';
import apiFetch from '@wordpress/api-fetch';

declare const window: {
    GiveDonorOptions: GiveDonorOptions;
} & Window;

/**
 * @unreleased
 */
export function useDonorEntityRecord(donorId?: number) {
    const urlParams = new URLSearchParams(window.location.search);

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
    } = useEntityRecord('givewp', 'donor', donorId ?? urlParams.get('id'));

    return {donor, hasResolved, save, edit};
}

/**
 * @unreleased
 */
export function getDonorOptionsWindowData(): GiveDonorOptions {
    return window.GiveDonorOptions;
}

export function handleTooltipDismiss(id: string) {
    return apiFetch({
        url: window.GiveDonorOptions.adminUrl + '/admin-ajax.php?action=' + id,
        method: 'POST',
    })
}

/**
 * @unreleased
 */
export function amountFormatter(currency: Intl.NumberFormatOptions['currency'], options?: Intl.NumberFormatOptions): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options
    });
}

/**
 * @unreleased
 */
export async function updateUserNoticeOptions(metaKey: string){
    try {
        const currentUser = await apiFetch( { path: '/wp/v2/users/me' } );
        // @ts-ignore
        const currentUserId = currentUser?.id;

        return await wp.data.dispatch('core').saveEntityRecord('root', 'user', {
            id: currentUserId,
            meta: {
                [metaKey]: true
            }
        });
    } catch (error) {
        console.error('Error updating user meta:', error);
    }
}
