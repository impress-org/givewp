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
            forms?: Array<{value: number; label: string}>;
            table?: {columns: Array<object>};
            paymentMode?: boolean;
            manualDonations?: boolean;
            donationDetails?: DataValues;
        };
    }
}

export const {donationDetails: data, apiRoot, apiNonce} = window.GiveDonations;
export const endpoint = `${apiRoot}/${data.id}`;
