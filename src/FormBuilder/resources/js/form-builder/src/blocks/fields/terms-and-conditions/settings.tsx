import {__} from '@wordpress/i18n';

import Edit from './Edit';
import Icon from './Icon';

import {FieldBlock} from '@givewp/form-builder/types';
import defaultSettings from '../settings';

const settings: FieldBlock['settings'] = {
    ...defaultSettings,
    title: __('Terms and conditions', 'give'),
    description: __('Donors can accept the terms and conditions', 'give'),
    supports: {
        multiple: false,
        html: true,
    },
    attributes: {
        useGlobalSettings: {
            type: 'boolean',
            source: 'attribute',
            default: 'true',
        },
        checkboxLabel: {
            type: 'string',
            source: 'attribute',
            default: __('I agree to the Terms and conditions.', 'give'),
        },
        displayType: {
            type: 'string',
            source: 'attribute',
            default: 'showFormTerms',
        },
        linkText: {
            type: 'string',
            source: 'attribute',
            default: __('Show terms', 'give'),
        },
        linkUrl: {
            type: 'string',
            source: 'attribute',
            default: '',
        },
        agreementText: {
            type: 'string',
            default: __(
                '<p>Acceptance of any contribution, gift or grant is at the discretion of the GiveWP.</p>' +
                    '<p>The GiveWP will not accept any gift unless it can be used or expended consistently with the purpose and mission of the GiveWP.</p>' +
                    '<p>No irrevocable gift, whether outright or life-income in character, will be accepted if under any reasonable set of circumstances the gift would jeopardize the donor’s financial security.</p>' +
                    '<p>The GiveWP will refrain from providing advice about the tax or other treatment of gifts and will encourage donors to seek guidance from their own professional advisers to assist them in the process of making their donation.</p>',
                'give'
            ),
        },
        modalHeading: {
            type: 'string',
            source: 'attribute',
            default: __('Do you consent to the following', 'give'),
        },
        modalAcceptanceText: {
            type: 'string',
            source: 'attribute',
            default: __('Accept', 'give'),
        },
    },
    edit: Edit,
    icon: Icon,
};

export default settings;
