<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;

/**
 * Class Utils
 *
 * @since 2.9.0
 */
class Utils
{
    /**
     * Returns whether or not the PayPal Commerce gateway is active
     *
     * @since 2.9.0
     *
     * @return bool
     */
    public static function gatewayIsActive()
    {
        return give_is_gateway_active(PayPalCommerce::GATEWAY_ID);
    }

    /**
     * Return whether or not payment gateway accept payment.
     *
     * @since 2.9.6
     * @return bool
     */
    public static function isAccountReadyToAcceptPayment()
    {
        /* @var MerchantDetail $merchantDetail */
        $merchantDetail = give(MerchantDetail::class);

        return (bool)$merchantDetail->accountIsReady;
    }
}
