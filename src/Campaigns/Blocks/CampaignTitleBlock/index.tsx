import {BlockConfiguration, getBlockType, registerBlockType} from '@wordpress/blocks';
import GiveIcon from '@givewp/components/GiveIcon';
import Edit from './edit';

import schema from './block.json';

if (!getBlockType(schema.name)) {
    registerBlockType(schema as BlockConfiguration, {
        icon: <GiveIcon color="grey" />,
        edit: Edit,
    });
}
