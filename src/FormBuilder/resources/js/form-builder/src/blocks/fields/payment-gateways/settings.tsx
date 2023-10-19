import {__} from '@wordpress/i18n';
import defaultSettings from '../settings';
import {FieldBlock} from '@givewp/form-builder/types';
import Edit from './Edit';
import Icon from './Icon';

const settings: FieldBlock['settings'] = {
    ...defaultSettings,
    title: __('Payment Gateways', 'give'),
    description: __('Display payment gateway options for donors to process their donation.', 'give'),
    supports: {
        multiple: false,
    },
    attributes: {},
    edit: Edit,
    icon: Icon,
};

export default settings;
