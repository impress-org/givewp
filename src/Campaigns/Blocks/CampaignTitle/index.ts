import {heading as icon} from '@wordpress/icons';
import schema from './block.json';
import Edit from './edit';

/**
 * @since 4.0.0
 */
const settings = {
    icon,
    edit: Edit,
};

export default {
    schema,
    settings,
};
