import {BlockConfiguration, getBlockType, registerBlockType} from '@wordpress/blocks';
import Edit from './edit';
import Icon from './icon';

import schema from './block.json';

if (!getBlockType(schema.name)) {
    registerBlockType(schema as BlockConfiguration, {
        icon: <Icon />,
        edit: Edit,
    });
}


