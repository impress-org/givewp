import {expect, RequestUtils} from '@wordpress/e2e-test-utils-playwright';
import {editForm} from './form';

/**
 * Creates a campaign and returns it with the v3 donation form that came with it.
 *
 * Campaigns are how v3 forms get made: `CreateDefaultCampaignForm` runs on `givewp_campaign_created`
 * and the new form's id comes back as the campaign's default. Specs create their own rather than
 * reach for whatever a site already has, so a run means the same thing on a fresh CI install and on
 * a populated developer site.
 */
export async function createCampaignWithForm(
    requestUtils: RequestUtils
): Promise<{title: string; campaignId: number; formId: number}> {
    const title = `E2E donation form ${Date.now()}`;

    const campaign = await requestUtils.rest<{id: number; defaultFormId: number}>({
        method: 'POST',
        path: '/givewp/v3/campaigns',
        data: {title, goal: 1000, goalType: 'amount'},
    });

    expect(campaign.defaultFormId, 'Creating a campaign should have created its default donation form').toBeTruthy();

    /*
     * A campaign's default form is published, but the settings it is created with leave
     * `formStatus` at its `draft` default. Anything that saves the form afterwards writes that
     * status back onto the post and takes the form off the front end, so square the two before a
     * spec touches it.
     */
    await editForm(requestUtils, campaign.defaultFormId, (form) => {
        form.settings.formStatus = 'publish';
    });

    return {title, campaignId: campaign.id, formId: campaign.defaultFormId};
}
