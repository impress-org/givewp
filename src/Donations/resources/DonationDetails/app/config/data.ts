import {DataValues} from '../components/FormTemplate/types';

/**
 *
 * @unreleased
 */
declare global {
    interface Window {
        GiveDonationsDetails: {
            apiNonce: string;
            apiRoot: string;
            adminUrl: string;
            donationDetails?: DataValues;
        };
    }
}

export const {donationDetails: data, apiRoot: endpoint} = window.GiveDonationsDetails;
