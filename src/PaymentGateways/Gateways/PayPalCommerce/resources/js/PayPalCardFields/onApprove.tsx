import type { CardFieldsOnApproveData } from "@paypal/paypal-js";
import {PayPalCommerceGateway} from '../../../types';

// TODO: replace with GiveWP endpoint for approving order
export default async function onApprove(data: CardFieldsOnApproveData, gateway: PayPalCommerceGateway){
    try {
        // orderID comes from createOrder callback
        console.log('onApprove', { orderID: data.orderID });

        gateway.payPalOrderId = data.orderID;

        return true;
    } catch (err) {
        console.error(err);
    }
}
