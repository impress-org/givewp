import {__} from '@wordpress/i18n';
import {PageInformation} from '@givewp/components/AdminUI/FormPage/types';

/**
 *
 * @unreleased
 */

const {id} = window.GiveDonations.donationDetails;

export const pageInformation: PageInformation = {
    id: id,
    description: __('Donation ID', 'give'),
    title: __('Donation', 'give'),
};
