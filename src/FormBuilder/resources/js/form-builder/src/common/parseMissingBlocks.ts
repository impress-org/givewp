import {BlockInstance, getBlockType, getUnregisteredTypeHandlerName} from '@wordpress/blocks';

/**
 * Parses the blocks and replaces any missing blocks with the core missing block type.
 *
 * The way this works is that when a block is missing, we replace it with the missing block type and store the original block type json from the database in the originalContent attribute.
 * Then, when the block is found again as a registered block, we replace the missing block with the stored originalContent attribute.
 *
 * @see https://github.com/WordPress/gutenberg/tree/trunk/packages/block-library/src/missing
 *
 * @since 3.0.0
 */
export default function parseMissingBlocks(blocks: BlockInstance[]){
    blocks.forEach((sectionBlock, sectionBlockIndex) => {
        sectionBlock.innerBlocks?.forEach((innerBlock, innerBlockIndex) => {
            const blockType = getBlockType(innerBlock.name);

            if (blockType === undefined) {
                const coreMissingBlockType = getBlockType(getUnregisteredTypeHandlerName());

                const missingBlockTypeReplacement = {
                    ...coreMissingBlockType,
                    attributes: {
                        ...coreMissingBlockType.attributes,
                        originalName: innerBlock.name,
                        originalContent: JSON.stringify(innerBlock),
                        originalUndelimitedContent: '',
                    },
                };

                blocks[sectionBlockIndex].innerBlocks[innerBlockIndex] = {
                    ...innerBlock,
                    ...missingBlockTypeReplacement,
                };
            } else if (blockType.name === getUnregisteredTypeHandlerName()) {
                const originalBlockType = getBlockType(innerBlock.attributes.originalName);

                if (originalBlockType !== undefined) {
                    blocks[sectionBlockIndex].innerBlocks[innerBlockIndex] = JSON.parse(
                        innerBlock.attributes.originalContent
                    );
                }
            }
        });
    });
};