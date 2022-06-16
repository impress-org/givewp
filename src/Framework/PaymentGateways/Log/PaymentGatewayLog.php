<?php

namespace Give\Framework\PaymentGateways\Log;

use Give\Donations\Models\Donation;
use Give\Log\Log;

/**
 * @since 2.21.0 Remove GatewayPaymentData related code and Update 'source' to payment gateway name
 * @since 2.19.6 remove cardInfo from log
 * @since 2.18.0
 */
class PaymentGatewayLog extends Log
{
    /**
     * @inheritDoc
     */
    public static function __callStatic($name, $arguments)
    {
        $arguments[1]['category'] = 'Payment Gateway';
        $arguments[1]['source'] = 'Payment Gateway';

        if (
            array_key_exists('Donation', $arguments[1]) &&
            $arguments[1]['Donation'] instanceof Donation
        ) {
            $arguments[1]['source'] = $arguments[1]['Donation']->gateway()->getName();
        }

        parent::__callStatic($name, $arguments);
    }
}
