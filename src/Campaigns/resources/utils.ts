import { useEntityRecord } from '@wordpress/core-data';
import { Campaign } from '@givewp/campaigns/admin/components/types';
import type { GiveCampaignOptions } from '@givewp/campaigns/types';
import apiFetch from '@wordpress/api-fetch';

declare const window: {
    GiveCampaignOptions: GiveCampaignOptions;
} & Window;

/**
 * @unreleased Return "record" instead of "campaign"
 * @since 4.0.0
 */
export function useCampaignEntityRecord(campaignId?: number) {
    const urlParams = new URLSearchParams(window.location.search);

    const {
        record: campaign,
        hasResolved,
        isResolving,
        save,
        edit,
    }: {
        record: Campaign;
        hasResolved: boolean;
        isResolving: boolean;
        save: () => any;
        edit: (data: Campaign | Partial<Campaign>) => void;
    } = useEntityRecord('givewp', 'campaign', campaignId ?? urlParams.get('id'));

    return { record: campaign, hasResolved, isResolving, save, edit };
}

/**
 * @since 4.0.0
 */
export function getCampaignOptionsWindowData(): GiveCampaignOptions {
    return window.GiveCampaignOptions;
}

export function handleTooltipDismiss(id: string) {
    return apiFetch({
        url: window.GiveCampaignOptions.adminUrl + '/admin-ajax.php?action=' + id,
        method: 'POST',
    })
}

/**
 * @since 4.0.0
 */
export function amountFormatter(currency: Intl.NumberFormatOptions['currency'], options?: Intl.NumberFormatOptions): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options
    });
}

/**
 * @since 4.0.0
 */
export async function updateUserNoticeOptions(metaKey: string) {
    try {
        const currentUser = await apiFetch({ path: '/wp/v2/users/me' });
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

/**
 * @since 4.0.0
 */
export async function createCampaignPage(campaignId: number) {
    try {
        const response = await apiFetch({
            path: `/givewp/v3/campaigns/${campaignId}/page`,
            method: 'POST'
        });

        return response as {
            id: number,
        };
    } catch (error) {
        console.error('Error creating Campaign page:', error);
    }
}

/**
 * @unreleased
 */
export async function updateCampaignStatus(campaignId: number, status: 'archived' | 'active') {
    try {
        const response = await apiFetch({
            path: `/givewp/v3/campaigns/${campaignId}`,
            method: 'PUT',
            data: { status }
        });

        return response as Campaign;
    } catch (error) {
        console.error('Error updating campaign status:', error);
    }
}
