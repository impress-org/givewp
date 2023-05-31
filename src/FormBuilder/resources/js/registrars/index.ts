import BlockRegistrar from './blocks';
import sectionBlocks from '@givewp/form-builder/blocks/section';
import elementBlocks from '@givewp/form-builder/blocks/elements';
import fieldBlocks from '@givewp/form-builder/blocks/fields';

declare global {
    interface Window {
        // TODO: update window global types to be scoped to parent folder
        // @ts-ignore
        givewp: {
            form: {
                blocks: BlockRegistrar;
            };
        };
    }
}

if (!window.givewp) {
    // @ts-ignore
    window.givewp = {
        // @ts-ignore
        form: {},
    };
}

// @ts-ignore
window.givewp.form.blocks = new BlockRegistrar();

// register core blocks
[...sectionBlocks, ...elementBlocks, ...fieldBlocks].forEach(({name, settings}) => {
    window.givewp.form.blocks.register(name, settings);
});