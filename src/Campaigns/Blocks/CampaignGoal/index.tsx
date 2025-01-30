import metadata from './block.json';
import Edit from './edit';
import Icon from './icon';
import initBlock from '../shared/utils/init-block';

const {name} = metadata;

/**
 * @unreleased
 */
export const init = () => initBlock({
    name,
    metadata,
    settings: {
        edit: Edit,
        save: () => null,
        icon: <Icon />,
    }
});


