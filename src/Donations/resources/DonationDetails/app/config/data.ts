import {DataValues} from '../components/FormTemplate/types';

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
            donationDetails?: DataValues;
        };
    }
}

export const {donationDetails: data, apiRoot} = window.GiveDonations;
export const endpoint = `${apiRoot}/${data.id}`;
