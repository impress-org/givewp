import {__} from '@wordpress/i18n';
import {data} from './data';
import {PageInformation} from '@givewp/components/AdminUI/FormPage/types';

/**
 *
 * @unreleased
 */
export const pageInformation: PageInformation = {
    id: data.id,
    description: __('Donation ID', 'give'),
    title: __('Donation', 'give'),
};
