/**
 * @unreleased
 */
export type GiveDonorOptions = {
    isAdmin: boolean;
    adminUrl: string;
    apiRoot: string;
    apiNonce: string;
    donorsAdminUrl: string;
    currency: string;
    isRecurringEnabled: boolean;
    defaultForm: string;
    admin: {
        showCampaignInteractionNotice: boolean
        showFormGoalNotice: boolean
        showExistingUserIntroNotice: boolean
        showCampaignListTableNotice: boolean
        showCampaignFormNotice: boolean
        showCampaignSettingsNotice: boolean
    }
}
