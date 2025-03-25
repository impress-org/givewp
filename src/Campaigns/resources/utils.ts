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

/**
 * @unreleased
 */
export async function createLandingPage(campaignId: number, title: string, description: string = '') {
    try {
        const content = `
            <!-- wp:givewp/campaign-cover-block {"campaignId":${campaignId}} /-->
            <!-- wp:givewp/campaign-goal {"campaignId":${campaignId}} /-->
            <!-- wp:givewp/campaign-donate-button {"campaignId":${campaignId}} /-->
            <!-- wp:paragraph --><p>${description}</p><!-- /wp:paragraph -->
            <!-- wp:givewp/campaign-donations {"campaignId":${campaignId}} /-->
            <!-- wp:givewp/campaign-donors {"campaignId":${campaignId}} /-->
        `;

        const newPage = await wp.data.dispatch('core').saveEntityRecord('postType', 'page', {
            title,
            content: content.trim(),
            status: 'publish',
            meta: {
                campaignId
            }
        });

        console.log('Campaign page created:', newPage);
        return newPage;
    } catch (error) {
        console.error('Error creating Campaign page:', error);
    }
}
