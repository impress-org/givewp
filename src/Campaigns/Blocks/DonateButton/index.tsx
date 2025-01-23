import metadata from './block.json';
import Edit from './edit';
import initBlock from '../shared/utils/init-block';
import GiveIcon from '@givewp/components/GiveIcon';

const {name} = metadata;

export const init = () => initBlock({
    name,
    metadata,
    settings: {
        edit: Edit,
        save: () => null,
        icon: <GiveIcon color="grey" />,
    }
});
