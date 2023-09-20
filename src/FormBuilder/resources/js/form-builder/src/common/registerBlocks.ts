import {BlockSupports, registerBlockType, setUnregisteredTypeHandlerName} from '@wordpress/blocks';
import {__experimentalGetCoreBlocks} from '@wordpress/block-library';
import {getBlockRegistrar} from '@givewp/form-builder/common/getWindowData';

/**
 * Registers the missing block from WordPress core.
 *
 * @see https://github.com/WordPress/gutenberg/tree/trunk/packages/block-library/src/missing
 *
 * @since 3.0.0
 */
const registerMissingBlock = () => {
    //TODO: should probably replace this with a custom block
    const missingBlock = __experimentalGetCoreBlocks().find((block) => {
        return block.name === 'core/missing';
    });

    if (missingBlock) {
        const {name, metadata, settings} = missingBlock;

        registerBlockType({name, ...metadata}, settings);

        setUnregisteredTypeHandlerName(name);
    }
};

const blockRegistrar = getBlockRegistrar();

/**
 * @since 3.0.0
 */
const supportOverrides: BlockSupports = {
    customClassName: false,
    html: false,
};

/**
 * @since 3.0.0
 */
export default function registerBlocks(): void {
    registerMissingBlock();

    blockRegistrar.getAll().forEach(({name, settings}) => {
        // @ts-ignore
        registerBlockType(name, {
            ...settings,
            supports: {
                ...settings.supports,
                ...supportOverrides,
            },
        });
    });
}
