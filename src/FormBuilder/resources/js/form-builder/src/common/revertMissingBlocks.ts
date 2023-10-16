import {BlockInstance, getBlockType, getUnregisteredTypeHandlerName} from '@wordpress/blocks';

/**
 * Replaces any missing blocks with the original block type.
 *
 * This is used to preserve the original block type definition when persisting the blocks to the database.
 *
 * @since 3.0.0
 */
export default function revertMissingBlocks(blocks: BlockInstance[]) {
    blocks.forEach((sectionBlock, sectionBlockIndex) => {
        sectionBlock.innerBlocks?.forEach((innerBlock, innerBlockIndex) => {
            const blockType = getBlockType(innerBlock.name);

            if (blockType.name === getUnregisteredTypeHandlerName() && innerBlock.attributes?.originalContent) {
                blocks[sectionBlockIndex].innerBlocks[innerBlockIndex] = JSON.parse(
                    innerBlock.attributes.originalContent
                );
            }
        });
    });
}
