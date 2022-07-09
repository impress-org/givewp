import {registerBlockType} from "@wordpress/blocks";
import {__} from "@wordpress/i18n"
import Edit from './edit.js'
import Defaults from './defaults'

const {attributes} = Defaults

registerBlockType('custom-block-editor/section', {
    ...Defaults,
    title: __('Section', 'custom-block-editor'),
    attributes: {
        ...attributes,
        innerBlocksTemplate: {
            default: [
                ['custom-block-editor/field', {}],
            ]
        },
    },
    edit: Edit,
});

registerBlockType('custom-block-editor/donor-info', {
    ...Defaults,
    title: __('Donor Information', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        ...attributes,
        title: {
            default: 'Who\'s Giving Today?',
        },
        description: {
            default: 'We\'ll never share this information with anyone.',
        },
        showHonorific: {
            type: 'boolean',
            default: true,
        },
        innerBlocksTemplate: {
            default: [
                ['custom-block-editor/name-field-group', {lock: {remove: true}}],
                ['custom-block-editor/email-field'],
            ]
        },
    },
    edit: Edit,
});

registerBlockType('custom-block-editor/donation-amount', {
    ...Defaults,
    title: __('Donation Amount', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        ...attributes,
        title: {
            default: 'How much would you like to donate today?',
        },
        description: {
            default: 'All donations directly impact our organization and help us further our mission.',
        },
        innerBlocksTemplate: {
            default: [
                ['custom-block-editor/donation-amount-levels', {lock: {remove: true}}],
            ]
        },
    },
    edit: Edit,
});

registerBlockType('custom-block-editor/payment-details', {
    ...Defaults,
    title: __('Payment Details', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        ...attributes,
        title: {
            default: __('Payment Details', 'custom-block-editor')
        },
        description: {
            default: 'PAYMENT DETAILS WILL GO HERE :)',
        },
        innerBlocksTemplate: {
            default: [
                ['custom-block-editor/payment-gateways', {lock: {remove: true}}],
            ]
        },
    },
    edit: Edit,
});
