import {PayPalCommerceGateway} from '../../types';

export default async function authorizeOrder(
    url: string,
    gateway: PayPalCommerceGateway,
    formData: FormData,
    orderId: string
) {
    formData.append('orderId', orderId);

    const response = await fetch(url, {
        method: 'POST',
        body: formData,
    });

    const responseJson = await response.json();

    if (!responseJson.success) {
        throw responseJson.data.error;
    }

    const authorizationId = responseJson.data.id;

    gateway.payPalAuthorizationId = authorizationId;

    return authorizationId;
}
