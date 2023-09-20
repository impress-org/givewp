import {Icon} from '@wordpress/icons';
import {__} from '@wordpress/i18n';
import {SectionBlock} from '@givewp/form-builder/types/block';
import Edit from './Edit';
import {Path, SVG} from '@wordpress/components';

const settings: SectionBlock['settings'] = {
    title: __('Section', 'give'),
    category: 'section',
    description: __('A section groups form fields and content together.', 'give'),
    icon: () => (
        <Icon
            icon={
                <SVG width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        d="M5 3H3V5H5V3ZM9 3H7V5H9V3ZM7 19H9V21H7V19ZM5 7H3V9H5V7ZM19 7H21V9H19V7ZM5 11H3V13H5V11ZM19 11H21V13H19V11ZM5 15H3V17H5V15ZM19 15H21V17H19V15ZM5 19H3V21H5V19ZM11 3H13V5H11V3ZM13 19H11V21H13V19ZM15 3H17V5H15V3ZM17 19H15V21H17V19ZM19 3H21V5H19V3ZM21 19H19V21H21V19Z"
                        fill="currentColor"
                    />
                </SVG>
            }
        />
    ),

    supports: {
        html: false, // Removes support for an HTML mode,
        // @ts-ignore
        givewp: {
            conditionalLogic: true,
        },
    },

    attributes: {
        title: {
            type: 'string',
            source: 'attribute',
            selector: 'h1',
            default: __('Section Title', 'give'),
        },
        description: {
            type: 'string',
            source: 'attribute',
            selector: 'p',
            default: __('Section Description', 'give'),
        },
        allowedBlocks: {
            default: true,
        },
        innerBlocksTemplate: {
            default: [],
        },
    },
    edit: Edit,
    save: function () {
        return null; // Save as attributes - not rendered HTML.
    },
};

export default settings;
