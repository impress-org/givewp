/**
 * PayPal Commerce Platform: standard address.
 *
 * @unreleased
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
 * @unreleased
 */
export type PayPalShippingAddress = {
    name: {
        full_name: string,
    },
    address: PayPalAddress,
};

/**
 * PayPal Commerce Platform: subscriber
 *
 * @see https://developer.paypal.com/docs/api/subscriptions/v1/#subscriptions_create!path=subscriber&t=request
 * @unreleased
 */
export type PayPalSubscriber = {
    name: {
        given_name: string,
        surname: string
    },
    email_address: string,
    shipping_address?: PayPalShippingAddress,
};
