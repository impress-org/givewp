import schema from './block.json';
import Edit from './edit';
import {GalleryIcon} from './Icon';

/**
 * @unreleased
 */
const settings = {
    icon: <GalleryIcon />,
    edit: Edit,
};

export default {
    schema,
    settings,
};
