import metadata from './block.json';
import Edit from './edit';
import initBlock from '../shared/utils/init-block';

const {name} = metadata;

export {metadata, name};
export const settings = {
    edit: Edit,
};

export const init = () => initBlock({name, metadata, settings});
