import {BlockConfiguration, getBlockType, registerBlockType} from '@wordpress/blocks';
import Edit from './edit';
import {GalleryIcon} from './Icon';

import schema from './block.json';

if (!getBlockType(schema.name)) {
    registerBlockType(schema as BlockConfiguration, {
        icon: <GalleryIcon />,
        edit: Edit,
    });
}
