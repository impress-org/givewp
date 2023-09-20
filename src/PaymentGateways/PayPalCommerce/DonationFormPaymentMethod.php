<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\Gateways\PayPalCommerce\PayPalCommerceGateway;

/**
 * Class DonationFormPaymentMethod
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.9.6
 */
class DonationFormPaymentMethod
{
    /**
     *  Setup filter hook.
     *
     * @since 2.9.6
     */
    public function handle()
    {
        // Exit.
        if (! Utils::gatewayIsActive()) {
            return;
        }

        add_filter('give_enabled_payment_gateways', [$this, 'filterEnabledPayments'], 99);
    }

    /**
     * Disable PayPal payment option if gateway account is not setup.
     *
     * @since 3.0.0 Use new payment gateway class.
     * @sicne 2.9.6
     *
     * @param array $gateways
     *
     * @return array
     */
    public function filterEnabledPayments($gateways)
    {
        /* @var PayPalCommerceGateway $paypalCommerce */
        $paypalCommerce = give(PayPalCommerceGateway::class);

        if (! array_key_exists($paypalCommerce->getId(), $gateways)) {
            return $gateways;
        }

        if (! Utils::isAccountReadyToAcceptPayment()) {
            unset($gateways[$paypalCommerce->getId()]);
        }

        return $gateways;
    }
}
