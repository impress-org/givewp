import {BlockSupports, registerBlockType} from '@wordpress/blocks';

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