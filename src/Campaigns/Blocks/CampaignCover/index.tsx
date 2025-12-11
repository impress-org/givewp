import schema from './block.json';
import Edit from './edit';
import {GalleryIcon} from './Icon';

/**
 * @since 4.0.0
 */
const settings = {
    icon: <GalleryIcon />,
    edit: Edit,
};

export default {
    schema,
    settings,
};
