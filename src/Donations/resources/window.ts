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
            donationApiRoot?: string;
            adminUrl: string;
            forms?: Array<{value: string; text: string}>;
            table?: {columns: Array<object>};
            paymentMode?: boolean;
            manualDonations?: boolean;
            donationDetails?: DataValues;
        };
    }
}

export const {donationDetails: data, donationApiRoot, apiNonce} = window.GiveDonations;
export const endpoint = `${donationApiRoot}/${data.id}`;
