import type {PayPalCommerceGateway} from '../../types';

/**
 * @since 4.0.0
 */
export default async function createOrder(url: string, gateway: PayPalCommerceGateway, formData: FormData) {
    const response = await fetch(url, {
        method: 'POST',
        body: formData,
    });

    const responseJson = await response.json();

    if (!responseJson.success) {
        throw responseJson.data.error;
    }

    const orderId = responseJson.data.id;

    gateway.payPalOrderId = orderId;

    return orderId;
}
