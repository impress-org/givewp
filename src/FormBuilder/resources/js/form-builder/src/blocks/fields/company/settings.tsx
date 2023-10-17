import {FieldBlock} from '@givewp/form-builder/types';
import {Icon} from '@wordpress/icons';
import {__} from '@wordpress/i18n';
import defaultSettings from '../settings';
import {Path, SVG} from '@wordpress/components';

const settings: FieldBlock['settings'] = {
    ...defaultSettings,
    title: __('Company', 'give'),
    description: __('Donors can input their company name', 'give'),
    supports: {
        multiple: false,
    },
    attributes: {
        label: {
            default: __('Company Name', 'give'),
        },
    },
    icon: () => (
        <Icon
            icon={
                <SVG width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        d="M18 2H6C4.897 2 4 2.897 4 4V21C4 21.2652 4.10536 21.5196 4.29289 21.7071C4.48043 21.8946 4.73478 22 5 22H19C19.2652 22 19.5196 21.8946 19.7071 21.7071C19.8946 21.5196 20 21.2652 20 21V4C20 2.897 19.103 2 18 2ZM18 20H6V4H18V20Z"
                        fill="currentColor"
                    />
                    <Path
                        d="M8 6H11V8H8V6ZM13 6H16V8H13V6ZM8 10H11V12H8V10ZM13 10.031H16V12H13V10.031ZM8 14H11V16H8V14ZM13 14H16V16H13V14Z"
                        fill="currentColor"
                    />
                </SVG>
            }
        />
    ),
};

export default settings;
