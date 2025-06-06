import { IntlTelInputSettings } from "@givewp/forms/propTypes";

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
    intlTelInputSettings: IntlTelInputSettings;
    nameTitlePrefixes: string[];
    countries: {[countryCode: string]: string};
    admin: {
        showCampaignInteractionNotice: boolean
        showFormGoalNotice: boolean
        showExistingUserIntroNotice: boolean
        showCampaignListTableNotice: boolean
        showCampaignFormNotice: boolean
        showCampaignSettingsNotice: boolean
    }
}
