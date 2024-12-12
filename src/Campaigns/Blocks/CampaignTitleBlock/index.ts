import {registerBlockType} from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';

// @ts-ignore
registerBlockType(metadata.name, {
    edit: Edit
});
