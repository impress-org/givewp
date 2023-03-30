import {DataValues} from './DonationDetails/app/components/FormTemplate/types';

/**
 *
 * @unreleased
 */
declare global {
    interface Window {
        GiveDonations: {
            apiNonce: string;
            apiRoot: string;
            adminUrl: string;
            forms?: Array<{value: string; text: string}>;
            table?: {columns: Array<object>};
            paymentMode?: boolean;
            manualDonations?: boolean;
            donationDetails?: DataValues;
            currencyFormat?: {
                currency_position: string;
                decimal_separator: string;
                number_decimals: number;
                thousands_separator: string;
            };
        };
    }
}

export const {donationDetails: data, apiRoot, apiNonce, currencyFormat} = window.GiveDonations;
export const endpoint = `${apiRoot}/${data.id}`;
