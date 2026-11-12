import {expect, RequestUtils} from '@wordpress/e2e-test-utils-playwright';

/**
 * Creates a campaign and returns it with the v3 donation form that came with it.
 *
 * Campaigns are how v3 forms get made: `CreateDefaultCampaignForm` runs on `givewp_campaign_created`
 * and the new form's id comes back as the campaign's default. Specs create their own rather than
 * reach for whatever a site already has, so a run means the same thing on a fresh CI install and on
 * a populated developer site. `title` names the campaign; it defaults to a unique one.
 */
export async function createCampaignWithForm(
    requestUtils: RequestUtils,
    {title = `E2E donation form ${Date.now()}`}: {title?: string} = {}
): Promise<{title: string; campaignId: number; formId: number}> {
    const campaign = await requestUtils.rest<{id: number; defaultFormId: number}>({
        method: 'POST',
        path: '/givewp/v3/campaigns',
        data: {title, goal: 1000, goalType: 'amount'},
    });

    expect(campaign.defaultFormId, 'Creating a campaign should have created its default donation form').toBeTruthy();

    return {title, campaignId: campaign.id, formId: campaign.defaultFormId};
}
