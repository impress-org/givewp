import metadata from './block.json';
import Edit from './edit';
import initBlock from '../shared/utils/init-block';

const {name} = metadata;

export const init = () => initBlock({
    name,
    metadata,
    settings: {
        edit: Edit,
    }
});
