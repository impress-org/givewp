import type {PayPalCommerceGateway} from '../../types';
import {__} from '@wordpress/i18n';

/**
 * @since 4.16.7.1 Throw an Error carrying the server's message instead of a bare string, so the card
 *            fields error handler and the form can display it.
 * @since 4.0.0
 */
export default async function createOrder(url: string, gateway: PayPalCommerceGateway, formData: FormData) {
    const response = await fetch(url, {
        method: 'POST',
        body: formData,
    });

    const responseJson = await response.json();

    if (!responseJson.success) {
        const error = responseJson.data?.error;

        // The server answers with either a plain message or PayPal's own error object.
        throw new Error(
            typeof error === 'string' ? error : error?.message ?? __('Unable to create the PayPal order.', 'give')
        );
    }

    const orderId = responseJson.data.id;

    gateway.payPalOrderId = orderId;

    return orderId;
}
