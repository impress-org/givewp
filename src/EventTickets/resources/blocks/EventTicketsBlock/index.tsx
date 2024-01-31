import metadata from './block.json';
import Icon from './Icon';
import Edit from './Edit';

import './styles.scss';

/**
 * @unreleased
 */
const settings = {
    ...metadata,
    icon: Icon,
    edit: Edit,
};

const eventTicketsBlock = {
    name: settings.name,
    settings,
};

export default eventTicketsBlock;
