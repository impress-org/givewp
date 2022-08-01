import schema from './block.json';
import {BlockConfiguration, registerBlockType} from '@wordpress/blocks';
import Edit from './editor/Edit';

/**
 * @since 1.0.0
 */
registerBlockType(schema as BlockConfiguration, {
    edit: Edit,
});
