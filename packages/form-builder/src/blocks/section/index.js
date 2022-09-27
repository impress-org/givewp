import {__} from "@wordpress/i18n";
import Edit from './edit.js';
import Defaults from './defaults';

const {attributes} = Defaults;

const sectionBlocks = [
    {
        name: 'custom-block-editor/section',
        settings: {
            ...Defaults,
            title: __('Section', 'custom-block-editor'),
            attributes: {
                ...attributes,
                innerBlocksTemplate: {
                    default: [
                        ['custom-block-editor/field', {}],
                    ],
                },
            },
            edit: Edit,
        },
    },
];

const sectionBlockNames = sectionBlocks.map(section => section.name);

export default sectionBlocks;
export {
    sectionBlockNames,
};
