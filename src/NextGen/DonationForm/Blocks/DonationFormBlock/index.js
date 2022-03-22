import schema from './block.json';
import {registerBlockType} from '@wordpress/blocks';
import {useBlockProps} from '@wordpress/block-editor';

registerBlockType(schema, {
    edit: () => {
        const blockProps = useBlockProps();

        return <div {...blockProps}>It's all happening.</div>;
    },
});
