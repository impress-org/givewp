<?php

namespace Give\Framework\PaymentGateways\Log;

use Give\Log\Log;

/**
 * @unreleased
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

        parent::__callStatic($name, $arguments);
    }
}
