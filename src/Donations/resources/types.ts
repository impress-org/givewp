import { IntlTelInputSettings } from "@givewp/forms/propTypes";

type Gateway = {
    enabled: boolean;
    id: string;
    label: string;
    supportsRefund: boolean;
    supportsSubscriptions: boolean;
};

/**
 * @since 4.6.0
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
    isFeeRecoveryEnabled: boolean;
    eventTicketsEnabled: boolean;
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
    gateways: Gateway[];
}
