import {__} from '@wordpress/i18n';
import defaultSettings from '../settings';
import {FieldBlock} from '@givewp/form-builder/types';
import Edit from './Edit';
import {Icon} from '@wordpress/icons';
import {Path, SVG} from '@wordpress/components';

/**
 * @since 3.9.0
 */
const settings: FieldBlock['settings'] = {
    ...defaultSettings,
    title: __('Donor Phone', 'give'),
    description: __('Donors can input their phone number', 'give'),
    supports: {
        multiple: false,
    },
    attributes: {
        ...defaultSettings.attributes,
        label: {
            default: __('Phone Number', 'give'),
        },
        required: {
            type: 'boolean',
            source: 'attribute',
            default: false,
        },
    },
    icon: () => (
        <Icon
            icon={
                <SVG width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        fillRule="evenodd"
                        clipRule="evenodd"
                        d="M16.0554 21.8806C12.8621 20.9743 9.84919 19.2639 7.33755 16.7523C4.82592 14.2406 3.11557 11.2277 2.20923 8.03448C2.20386 8.01555 2.19853 7.99679 2.19324 7.97817C2.04573 7.45916 1.93134 7.05669 1.9297 6.50479C1.92782 5.87402 2.13333 5.08378 2.44226 4.53384C2.97354 3.58807 4.11431 2.37601 5.09963 1.87764C5.95097 1.44704 6.95637 1.44704 7.80771 1.87764C8.64995 2.30364 9.58794 3.2572 10.1109 4.06146C10.7573 5.0558 10.7573 6.33767 10.1109 7.33201C9.93761 7.59846 9.69068 7.84497 9.40402 8.13114C9.31476 8.22025 9.21651 8.28464 9.28176 8.42053C9.92958 9.76981 10.8131 11.0354 11.9337 12.1561C13.0544 13.2768 14.32 14.1603 15.6693 14.8081C15.81 14.8756 15.8654 14.7792 15.9587 14.6858C16.2449 14.3991 16.4914 14.1522 16.7578 13.979C17.7522 13.3325 19.034 13.3325 20.0284 13.979C20.8111 14.4879 21.7895 15.4465 22.2122 16.2821C22.6428 17.1335 22.6428 18.1389 22.2122 18.9902C21.7138 19.9755 20.5018 21.1163 19.556 21.6476C19.0061 21.9565 18.2158 22.162 17.585 22.1601C17.0331 22.1585 16.6307 22.0441 16.1117 21.8966C16.0931 21.8913 16.0743 21.886 16.0554 21.8806Z"
                        fill="currentColor"
                    />
                </SVG>
            }
        />
    ),
    edit: Edit,
};

export default settings;
