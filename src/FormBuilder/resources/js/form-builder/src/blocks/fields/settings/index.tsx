import {__} from '@wordpress/i18n';
import {BlockAttributes, BlockConfiguration} from '@wordpress/blocks';
import Edit from './Edit';

const defaultSettings: BlockConfiguration = {
    title: __('Field', 'give'),
    category: 'input',
    supports: {
        html: false, // Removes support for an HTML mode.
        multiple: true,
    },
    attributes: {
        fieldName: {
            type: 'string',
            source: 'attribute',
        },
        label: {
            type: 'string',
            source: 'attribute',
            default: __('Text Field', 'give'),
        },
        placeholder: {
            type: 'string',
            source: 'attribute',
            default: '',
        },
        isRequired: {
            type: 'boolean',
            source: 'attribute',
            default: false,
        },
        options: {
            type: 'array',
        },
    },

    edit: Edit,

    save: function () {
        return null; // Save as attributes - not rendered HTML.
    },
};

export default defaultSettings;

const {fieldName, label, placeholder, isRequired, options} = defaultSettings.attributes as BlockAttributes;

export {fieldName, label, placeholder, isRequired, options};
