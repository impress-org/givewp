import {__} from "@wordpress/i18n";
import type {BlockInstance} from '@wordpress/blocks';

/*
 * Enforce top-level section block hierarchy.
 */
export default (blocks: BlockInstance[]) => {
    return blocks.map((block) => {
        return block.name == 'givewp/section'
            ? block
            : {
                ...block,
                name: 'givewp/section',
                attributes: {
                    title: __('Section Title', 'give'),
                    description: __('Section Description', 'give'),
                    innerBlocksTemplate: [[block.name, block.attributes]],
                }
            }
    })
}
