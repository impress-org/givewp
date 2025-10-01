import { SelectOption } from './types';

/**
 * Formats campaign data into ReactSelect options
 *
 * @unreleased
 */
export function formatCampaignOptions(campaignsWithForms: Record<string, any>): SelectOption[] {
    if (!campaignsWithForms) {
        return [];
    }

    return Object.entries(campaignsWithForms).map(([campaignId, campaign]) => ({
        value: Number(campaignId),
        label: campaign?.title,
    }));
}

/**
 * Formats form data into ReactSelect options
 *
 * @unreleased
 */
export function formatFormOptions(campaignForms: Record<string, string>): SelectOption[] {
    if (!campaignForms) {
        return [];
    }

    return Object.entries(campaignForms).map(([formId, formTitle]) => ({
        value: Number(formId),
        label: formTitle,
    }));
}
