import Edit from './edit';
import schema from '../../CampaignComments/block.json';
import {paragraph as icon} from '@wordpress/icons';

const settings = {
    icon,
    edit: Edit,
};

export default {
    schema,
    settings,
};
