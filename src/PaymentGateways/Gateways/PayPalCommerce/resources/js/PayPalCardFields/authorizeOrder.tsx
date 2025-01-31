import type { CardFieldsOnApproveData } from "@paypal/paypal-js";
import type {PayPalCommerceGateway} from '../../../types';

export default async function authorizeOrder(cardData: CardFieldsOnApproveData, url: string, gateway: PayPalCommerceGateway, formData: FormData) {
    try {
        console.log('authorizeOrder', { orderID: cardData.orderID });

        formData.append('orderId', cardData.orderID);
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });

        const responseJson = await response.json();

        if (!responseJson.success) {
            throw responseJson.data.error;
        }

        const authorizationID = responseJson.data.id;

        gateway.payPalAuthorizationId = authorizationID;

        return authorizationID;
    } catch (err) {
        console.error(err);
    }
}
