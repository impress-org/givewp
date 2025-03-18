import {useEntityRecord} from '@wordpress/core-data';
import {Campaign} from '@givewp/campaigns/admin/components/types';
import type {GiveCampaignOptions} from '@givewp/campaigns/types';
import apiFetch from '@wordpress/api-fetch';

declare const window: {
    GiveCampaignOptions: GiveCampaignOptions;
} & Window;

/**
 * @unreleased
 */
export function useCampaignEntityRecord(campaignId?: number) {
    const urlParams = new URLSearchParams(window.location.search);

    const {
        record: campaign,
        hasResolved,
        save,
        edit,
    }: {
        record: Campaign;
        hasResolved: boolean;
        save: () => any;
        edit: (data: Campaign | Partial<Campaign>) => void;
    } = useEntityRecord('givewp', 'campaign', campaignId ?? urlParams.get('id'));

    return {campaign, hasResolved, save, edit};
}

/**
 * @unreleased
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
 * @unreleased
 */
export function amountFormatter(currency: Intl.NumberFormatOptions['currency'], options?: Intl.NumberFormatOptions): Intl.NumberFormat {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        ...options
    });
}
