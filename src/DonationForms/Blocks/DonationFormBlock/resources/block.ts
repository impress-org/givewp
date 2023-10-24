import schema from '../block.json';
import {BlockConfiguration, registerBlockType} from '@wordpress/blocks';
import Edit from './editor/Edit';
import GiveIcon from './Icon';

/**
 * @since 3.0.0
 */
registerBlockType(schema as BlockConfiguration, {
    icon: GiveIcon,
    edit: Edit,
});
