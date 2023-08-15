import {__} from '@wordpress/i18n';
import type {FieldBlock} from '@givewp/form-builder/types';
import {commentContent} from '@wordpress/icons';
import Edit from './Edit';
import {GiveWPSupports} from '@givewp/form-builder/supports/types';

/**
 * @since 3.0.0
 */
const givewp: GiveWPSupports = {
    fieldSettings: {
        label: {
            default: __('Comment', 'give'),
        },
        description: {
            default: __('Would you like to add a comment to this donation?', 'give'),
        },
        metaKey: false,
        placeholder: false,
        required: false,
        storeAsDonorMeta: false,
        displayInAdmin: false,
        displayInReceipt: false,
        defaultValue: false,
        emailTag: false,
    },
};

/**
 * @since 3.0.0
 */
const settings: FieldBlock['settings'] = {
    title: __('Donor Comments', 'give'),
    description: __(
        'Do you want to provide donors the ability to add a comment to their donation? The comment will display publicly on the donor wall if they do not select to give anonymously.',
        'give'
    ),
    category: 'input',
    supports: {
        html: false,
        multiple: false,
        // @ts-ignore
        givewp
    },
    icon: commentContent,
    edit: Edit,
    save: () => null,
};

export default settings;