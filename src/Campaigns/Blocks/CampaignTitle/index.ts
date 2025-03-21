import {heading as icon} from '@wordpress/icons';
import schema from './block.json';
import Edit from './edit';

/**
 * @unreleased
 */
const settings = {
    icon,
    edit: Edit,
};

export default {
    schema,
    settings,
};
