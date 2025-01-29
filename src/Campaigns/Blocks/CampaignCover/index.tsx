import metadata from './block.json';
import Edit from './edit';
import initBlock from '../shared/utils/init-block';
import {GalleryIcon} from './Icon';

const {name} = metadata;

export {metadata, name};
export const settings = {
    edit: Edit,
    icon: <GalleryIcon />,
};

export const init = () => initBlock({name, metadata, settings});
