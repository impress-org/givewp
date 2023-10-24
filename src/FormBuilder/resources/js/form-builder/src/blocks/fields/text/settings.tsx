import {__} from '@wordpress/i18n';
import defaultSettings from '../settings';
import {FieldBlock} from '@givewp/form-builder/types';
import Edit from './Edit';
import {Icon} from '@wordpress/icons';
import {Path, SVG} from '@wordpress/components';

const settings: FieldBlock['settings'] = {
    ...defaultSettings,
    title: __('Text Field', 'give'),
    category: 'custom',
    description: __('A custom text field that donors can use.', 'give'),
    supports: {
        // @ts-ignore
        givewp: {
            fieldSettings: {
                label: {
                    default: __('Text field', 'give'),
                },
                description: true,
                placeholder: true,
                defaultValue: true,
                emailTag: true,
            },
            conditionalLogic: true,
        },
    },
    icon: () => (
        <Icon
            icon={
                <SVG width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        d="M10.8449 6.89062L7.05176 16.5H9.1684L9.89932 14.6484H14.1006L14.8316 16.5H16.9482L13.155 6.89062H10.8449ZM10.6765 12.6797L12 9.32658L13.3235 12.6797H10.6765Z"
                        fill="#000C00"
                    />
                    <Path
                        d="M18 2.625H6V0.75H0.75V6H2.625V18H0.75V23.25H6V21.375H18V23.25H23.25V18H21.375V6H23.25V0.75H18V2.625ZM2.25 4.5V2.25H4.5V4.5H2.25ZM4.5 21.75H2.25V19.5H4.5V21.75ZM18 19.875H6V18H4.125V6H6V4.125H18V6H19.875V18H18V19.875ZM21.75 19.5V21.75H19.5V19.5H21.75ZM19.5 2.25H21.75V4.5H19.5V2.25Z"
                        fill="#000C00"
                    />
                </SVG>
            }
        />
    ),
    edit: Edit,
};

export default settings;
