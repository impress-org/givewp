import {registerBlockType} from "@wordpress/blocks";
import {Icon} from '@wordpress/icons';
import {__} from "@wordpress/i18n"
import settings from "./settings"
import DonorName from "./donorName";

/**
 * @note Blocks in the appender are listed in the order that the blocks are registered.
 */

registerBlockType('custom-block-editor/company-field', {
    ...settings,
    title: __('Company', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        label: {
            default: __('Company Name'),
        },
    },
    icon: () => <Icon icon={
        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fillRule="evenodd"
                  d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z"
                  clipRule="evenodd"/>
        </svg>
    }/>,
});

registerBlockType('custom-block-editor/field', {
    ...settings,
    title: __('Custom Field', 'custom-block-editor'),
});

/*----------------------------------------*/

registerBlockType('custom-block-editor/honorific-name-field', {
    ...settings,
    title: __('Honorific', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        lock: {remove: true},
        label: {
            default: __('Honorific'),
        },
        options: {
            default: [
                {label: __('Mr.', 'give'), value: 'mr'},
                {label: __('Ms.', 'give'), value: 'ms'},
                {label: __('Mrs.', 'give'), value: 'mrs'},
            ],
        },
    },
});

registerBlockType('custom-block-editor/first-name-field', {
    ...settings,
    title: __('First Name', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        lock: {remove: true},
        label: {
            default: __('First Name'),
        },
    },
});

registerBlockType('custom-block-editor/last-name-field', {
    ...settings,
    title: __('Last Name', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        lock: {remove: true},
        label: {
            default: __('Last Name'),
        },
    },
});

registerBlockType('custom-block-editor/email-field', {
    ...settings,
    title: __('Email', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        lock: {remove: true},
        label: {
            default: __('Email Address'),
        },
    },
    icon: () => <Icon icon={
        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
        </svg>
    }/>,
});

registerBlockType('custom-block-editor/payment-gateways', {
    ...settings,
    parent: 'custom-block-editor/payment-details',
    title: __('Payment Gateways', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        lock: {remove: true},
    },
    edit: () => <div style={{
        padding: '20px',
        margin: '20px 0',
        textAlign: 'center',
        backgroundColor: '#fafafa'
    }}>{'Payment Gateways Go Here'}</div>
});

registerBlockType('custom-block-editor/donor-name', {
    ...settings,
    title: __('Donor Name', 'custom-block-editor'),
    supports: {
        multiple: false,
    },
    attributes: {
        lock: {remove: true},
        showHonorific: {
            type: 'boolean',
        },
        honoriphics: {
            type: 'array',
            default: true,
        },
        requireLastName: {
            type: 'boolean',
            default: false,
        }
    },
    edit: DonorName
});
