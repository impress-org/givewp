import {Icon} from '@wordpress/icons';
import {__} from '@wordpress/i18n';
import defaultSettings from '../settings';
import FieldSettings from '@givewp/form-builder/blocks/fields/settings/Edit';
import {FieldBlock} from '@givewp/form-builder/types';
import {Path, SVG} from '@wordpress/components';
import {BlockEditProps} from '@wordpress/blocks';

/**
 * @since 3.4.1 updated default required attribute to be true on block and edit component
 * @since 3.0.0
 */
const settings: FieldBlock['settings'] = {
    ...defaultSettings,
    title: __('Email', 'give'),
    description: __('The required email field for donors to enter their email address.', 'give'),
    supports: {
        multiple: false,
    },
    attributes: {
        ...defaultSettings.attributes,
        label: {
            default: __('Email Address'),
        },
        isRequired: {
            type: 'boolean',
            source: 'attribute',
            default: true,
        },
    },
    icon: () => (
        <Icon
            icon={
                <SVG width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        d="M22 6C22 4.9 21.1 4 20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6ZM20 6L12 11L4 6H20ZM20 18H4V8L12 13L20 8V18Z"
                        fill="currentColor"
                    />
                </SVG>
            }
        />
    ),

    edit: (props: BlockEditProps<any>) => <FieldSettings showRequired={false} {...props} attributes={{...props.attributes, isRequired: true}} />,
};

export default settings;
