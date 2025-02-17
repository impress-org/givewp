import edit from './edit';
import GiveIcon from '@givewp/components/GiveIcon';
import schema from './block.json';

/**
 * @unreleased
 */
export default {
    schema,
    settings: {
        icon: <GiveIcon color="grey" />,
        edit,
    },
};

