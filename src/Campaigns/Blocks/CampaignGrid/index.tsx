import edit from './edit';
import GiveIcon from '@givewp/components/GiveIcon';
import schema from './block.json';

/**
 * @since 4.0.0
 */
export default {
    schema,
    settings: {
        icon: <GiveIcon color="grey" />,
        edit,
    },
};

