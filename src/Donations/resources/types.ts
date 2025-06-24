import { IntlTelInputSettings } from "@givewp/forms/propTypes";

/**
 * @unreleased
 */
export type GiveDonationOptions = {
    isAdmin: boolean;
    adminUrl: string;
    apiRoot: string;
    apiNonce: string;
    donationsAdminUrl: string;
    currency: string;
    currencySymbol: string;
    isRecurringEnabled: boolean;
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
    isFeeRecoveryEnabled: boolean;
    donationStatuses: {[statusCode: string]: string};
    campaignsWithForms: {
        [campaignId: string]: {
            title: string;
            defaultFormId: string;
            forms: {
                [formId: string]: string;
            };
        };
    };
    donors: {
        [donorId: string]: string;
    };
    mode: 'test' | 'live';
}
