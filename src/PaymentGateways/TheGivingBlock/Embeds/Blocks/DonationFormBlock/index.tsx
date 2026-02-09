import schema from './block.json';
import Edit from './edit';
import TgbIcon from './Icon';

const settings = {
    icon: <TgbIcon color="tgb" />,
    edit: Edit,
    save: () => null,
};

/**
 * @unreleased
 */
export default {
    schema,
    settings,
};
