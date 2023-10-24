import {Icon} from '@wordpress/icons';
import {__} from '@wordpress/i18n';
import {ElementBlock} from '@givewp/form-builder/types/block';
import Edit from './Edit';

const settings: ElementBlock['settings'] = {
    title: __('Paragraph', 'give'),
    description: 'Place a styled paragraph in your form.',
    category: 'content',
    supports: {
        html: false,
        multiple: true,
        // @ts-ignore
        givewp: {
            conditionalLogic: true,
        },
    },
    attributes: {
        content: {
            type: 'string',
            source: 'attribute',
            selector: 'p',
            default: '',
        },
    },
    icon: () => (
        <Icon
            icon={
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="14.917" y1="20" x2="14.917" y2="4.88889" stroke="#1E1E1E" strokeWidth="1.5" />
                    <line x1="10.4727" y1="20" x2="10.4727" y2="4.88889" stroke="#1E1E1E" strokeWidth="1.5" />
                    <line x1="18.333" y1="4.75" x2="9.44412" y2="4.75" stroke="#1E1E1E" strokeWidth="1.5" />
                    <path
                        d="M9.13889 8.88889V12.96C7.21109 12.6071 5.75 10.9186 5.75 8.88889C5.75 6.85914 7.21109 5.17065 9.13889 4.81778V8.88889Z"
                        fill="#1E1E1E"
                        stroke="#1E1E1E"
                        strokeWidth="1.5"
                    />
                </svg>
            }
        />
    ),
    edit: Edit,
    save: function () {
        return null;
    },
};

export default settings;
