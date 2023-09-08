import BlockRegistrar from './blocks';
import sectionBlocks from '@givewp/form-builder/blocks/section';
import elementBlocks from '@givewp/form-builder/blocks/elements';
import fieldBlocks from '@givewp/form-builder/blocks/fields';
import {getBlockRegistrar} from '@givewp/form-builder/common/getWindowData';

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
    getBlockRegistrar().register(name, settings);
});
