import schema from './block.json';
import Edit from './edit';
import GiveIcon from '@givewp/components/GiveIcon';

/**
 * @unreleased
 */
const settings = {
    icon: <GiveIcon color="grey" />,
    edit: Edit,
    save: () => null,
};

export default {
    schema,
    settings,
};
