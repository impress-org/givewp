import {__} from '@wordpress/i18n';
import {data} from './data';

/**
 *
 * @unreleased
 */
export const pageInformation: {
    id: number;
    description: string;
    title: string;
} = {
    id: data.id,
    description: __('Donation ID', 'give'),
    title: __('Donation', 'give'),
};
