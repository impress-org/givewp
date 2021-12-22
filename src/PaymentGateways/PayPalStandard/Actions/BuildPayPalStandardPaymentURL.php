<?php

namespace Give\PaymentGateways\PayPalStandard\Actions;

use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;

/**
 * This class create PayPal Standard payment gateway one time payment url on basis of donor donation query.
 *
 * @unlreased
 */
class BuildPayPalStandardPaymentURL
{
    public function __invoke(GatewayPaymentData $paymentData)
    {
        // TODO: copy code from give_build_paypal_url function
        return '';
    }

}
