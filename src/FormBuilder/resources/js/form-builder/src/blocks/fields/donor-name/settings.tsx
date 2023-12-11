import {Icon} from '@wordpress/icons';
import {__} from '@wordpress/i18n';
import defaultSettings from '../settings';
import {FieldBlock} from '@givewp/form-builder/types';
import Edit from './Edit';
import {Path, SVG} from '@wordpress/components';

const settings: FieldBlock['settings'] = {
    ...defaultSettings,
    title: __('Donor Name', 'give'),
    description: __('Collects the donor name with display options.', 'give'),
    supports: {
        multiple: false,
    },
    attributes: {
        showHonorific: {
            type: 'boolean',
            default: true,
        },
        useGlobalSettings: {
            type: 'boolean',
            default: 'true',
        },
        honorifics: {
            type: 'array',
            default: ['Mr', 'Ms', 'Mrs'],
        },
        firstNameLabel: {
            type: 'string',
            source: 'attribute',
            default: __('First name', 'give'),
        },
        firstNamePlaceholder: {
            type: 'string',
            source: 'attribute',
            default: __('First name', 'give'),
        },
        lastNameLabel: {
            type: 'string',
            source: 'attribute',
            default: __('Last name', 'give'),
        },
        lastNamePlaceholder: {
            type: 'string',
            source: 'attribute',
            default: __('Last name', 'give'),
        },
        requireLastName: {
            type: 'boolean',
            default: false,
        },
    },
    edit: Edit,
    icon: () => (
        <Icon
            icon={
                <SVG width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        d="M14.2736 13.4026C14.1721 13.3682 13.5308 13.0689 13.9315 11.8076H13.9258C14.9704 10.6936 15.7686 8.90101 15.7686 7.13619C15.7686 4.42256 14.026 3 12.0006 3C9.97402 3 8.24093 4.4219 8.24093 7.13619C8.24093 8.90827 9.03473 10.7081 10.0857 11.8195C10.4954 12.9321 9.76281 13.3451 9.60966 13.4032C7.48861 14.1974 5 15.6451 5 17.0743V17.6101C5 19.5573 8.64613 20 12.0204 20C15.3998 20 19 19.5573 19 17.6101V17.0743C19 15.6022 16.4993 14.1657 14.2736 13.4026Z"
                        fill="currentColor"
                    />
                </SVG>
            }
        />
    ),
};

export default settings;
