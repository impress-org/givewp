import {BlockSupports, registerBlockType, setUnregisteredTypeHandlerName} from '@wordpress/blocks';
import {__experimentalGetCoreBlocks} from '@wordpress/block-library';

/**
 * Registers the missing block from WordPress core.
 *
 * @see https://github.com/WordPress/gutenberg/tree/trunk/packages/block-library/src/missing
 *
 * @since 0.6.0
 */
const registerMissingBlock = () => {
    //TODO: should probably replace this with a custom block
    const missingBlock = __experimentalGetCoreBlocks().find((block) => {
        return block.name === 'core/missing';
    });

    if (missingBlock) {
        const {name: missingBlockName} = missingBlock;
        missingBlock.init();

        setUnregisteredTypeHandlerName(missingBlockName);
    }
};

const blockRegistrar = window.givewp.form.blocks;

/**
 * @since 0.4.0
 */
const supportOverrides: BlockSupports = {
    customClassName: false,
    html: false,
};

/**
 * @since 0.4.0
 */
export default function registerBlocks(): void {
    registerMissingBlock();

    const [sectionBlock] = blockRegistrar.getAll();

    blockRegistrar.getAll().forEach(({name, settings}) => {
        // TODO: circle back to parent flexibility
        const parent = name !== sectionBlock.name ? [sectionBlock.name] : undefined;

        // @ts-ignore
        registerBlockType(name, {
            ...settings,
            parent,
            supports: {
                ...settings.supports,
                ...supportOverrides,
            },
        });
    });
}