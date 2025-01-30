import type {Gateway, GatewaySettings} from '@givewp/forms/types';
import type {PayPalCardFieldsComponent} from '@paypal/paypal-js';

/**
 * PayPal Commerce Platform: Standard address.
 *
 * @since 3.1.0
 */
export type PayPalAddress = {
    address_line_1: string,
    address_line_2: string,
    admin_area_2: string,
    admin_area_1: string,
    country_code: string,
    postal_code: string,
};

/**
 * PayPal Commerce Platform: Shipping address
 *
 * @see https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_revise!path=shipping_address&t=request
 * @since 3.1.0
 */
export type PayPalShippingAddress = {
    name: {
        full_name: string,
    },
    address: PayPalAddress,
};

/**
 * PayPal Commerce Platform: Subscriber
 *
 * @see https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_create!path=subscriber&t=request
 * @since 3.1.0
 */
export type PayPalSubscriber = {
    name: {
        given_name: string,
        surname: string
    },
    email_address: string,
    shipping_address?: PayPalShippingAddress,
};


export type PayPalCommerceGatewaySettings = {
    ajaxUrl: string,
    donationFormID: number,
    donationFormNonce: string,
    validateUrl: string,
    createOrderUrl: string,
    authorizeOrderUrl: string,
    nonce: string,
    sdkOptions: {
        "data-namespace": string,
        "client-id": string,
        "merchant-id": string,
        "components": string,
        "disable-funding": string,
        "intent": string,
        "vault": string,
        "data-partner-attribution-id": string,
        "data-client-token": string,
        "currency": string,
        "enable-funding": string
    }
}

export interface PayPalCommerceGateway extends Gateway {
    cardFieldsForm?: PayPalCardFieldsComponent;
    settings?: GatewaySettings & PayPalCommerceGatewaySettings;
    payPalOrderId?: string;
    payPalAuthorizationId?: string;
}
