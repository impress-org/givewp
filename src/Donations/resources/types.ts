import { IntlTelInputSettings } from "@givewp/forms/propTypes";

/**
 * @unreleased
 */
export type GiveDonationOptions = {
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
    states: {
        list: {[countryCode: string]: {[stateCode: string]: string}};
        labels: {[countryCode: string]: string};
        noStatesCountries: string[];
        statesNotRequiredCountries: string[];
    };
    admin: {
        showCampaignInteractionNotice: boolean
        showFormGoalNotice: boolean
        showExistingUserIntroNotice: boolean
        showCampaignListTableNotice: boolean
        showCampaignFormNotice: boolean
        showCampaignSettingsNotice: boolean
    };
    eventTicketsEnabled: boolean;
    isFeeRecoveryEnabled: boolean;
}
