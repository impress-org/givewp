import blocksJson from '@givewp/form-builder/blocks.json';

/**
 * Gets the block attributes defined in our blocks.json file.
 * These can be used as our default block attributes.
 *
 * @since 3.0.0
 */
const getDefaultBlockAttributes = (blockName: string): any => {
    // @ts-ignore
    return blocksJson.flatMap(({innerBlocks}) => innerBlocks).find(({name}) => name === blockName)?.attributes;
};

export default getDefaultBlockAttributes;
