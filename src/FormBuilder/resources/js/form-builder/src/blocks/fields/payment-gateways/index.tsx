import settings from './settings';
import {FieldBlock} from '@givewp/form-builder/types';
import {addFilter} from "@wordpress/hooks";
import withAdditionalPaymentGatewayNotice from './withAdditionalPaymentGatewayNotice';

const paymentGateways: FieldBlock = {
    name: 'givewp/payment-gateways',
    settings,
};

addFilter('editor.BlockEdit', 'givewp/stripe-payment-element', withAdditionalPaymentGatewayNotice, 999);

export default paymentGateways;
