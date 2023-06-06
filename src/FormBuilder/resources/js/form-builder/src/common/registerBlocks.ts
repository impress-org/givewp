import {BlockSupports, registerBlockType} from '@wordpress/blocks';

const blockRegistrar = window.givewp.form.blocks;

/**
 * @unreleased
 */
const supportOverrides: BlockSupports = {
    customClassName: false,
    html: false,
};

 /**
 * @unreleased
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