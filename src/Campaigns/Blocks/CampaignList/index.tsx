import metadata from './block.json';
import Edit from './edit';
import initBlock from '../shared/utils/init-block';
import GiveIcon from '@givewp/components/GiveIcon';

const {name} = metadata;

export {metadata, name};
export const settings = {
    edit: Edit,
    icon: <GiveIcon color="gray"/>,
};

export const init = () => initBlock({name, metadata, settings});
