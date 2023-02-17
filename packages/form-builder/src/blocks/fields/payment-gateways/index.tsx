import settings from './settings';
import {FieldBlock} from '@givewp/form-builder/types';

const paymentGateways: FieldBlock = {
    name: 'custom-block-editor/payment-gateways',
    settings,
};

export default paymentGateways;
