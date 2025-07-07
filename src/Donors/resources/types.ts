import { IntlTelInputSettings } from "@givewp/forms/propTypes";

/**
 * @since 4.4.0
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
    mode: 'test' | 'live';
    countries: {[countryCode: string]: string};
    states: {
        list: {[countryCode: string]: {[stateCode: string]: string}};
        labels: {[countryCode: string]: string};
        noStatesCountries: string[];
        statesNotRequiredCountries: string[];
    };
}
