import {Icon} from '@wordpress/icons';
import {__} from "@wordpress/i18n";
import settings from "../settings";

import Edit from './edit';

const amount = {

    name: 'custom-block-editor/donation-amount-levels', // @todo Rename this block.

    category: 'custom',

    settings: {
        ...settings,
        title: __('Donation Amount and Levels', 'custom-block-editor'),
        supports: {
            multiple: false,
            html: false, // Removes support for an HTML mode.
        },
        attributes: {
            levels: {
                type: 'array',
                default: ['10', '25', '50', '100', '250'],
            },
        },
        icon: () => <Icon icon={
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M11.5789 4.44118H20M4 7.52941L7.36842 4M7.36842 4V19M11.5789 8.85294H18.3158M11.5789 13.2647H16.6316M11.5789 17.6765H14.9474"
                    stroke="#000C00" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
        } />,
        edit: Edit,
    },
};

export default amount;
