import schema from './block.json';
import Edit from './edit';
import {StatsIcon} from './Icon';

/**
 * @since 4.0.0
 */
const settings = {
    icon: <StatsIcon />,
    edit: Edit,
};

export default {
    schema,
    settings,
};
