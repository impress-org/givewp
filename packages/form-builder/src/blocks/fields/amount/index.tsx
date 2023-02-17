import settings from './settings';
import {FieldBlock} from '@givewp/form-builder/types';

const amount: FieldBlock = {
    name: 'custom-block-editor/donation-amount-levels', // @todo Rename this block.
    settings,
};

export default amount;
