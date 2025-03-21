import schema from './block.json';
import Edit from './edit';
import {StatsIcon} from './Icon';

/**
 * @unreleased
 */
const settings = {
    icon: <StatsIcon />,
    edit: Edit,
};

export default {
    schema,
    settings,
};
