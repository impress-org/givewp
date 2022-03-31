<?php

namespace Give\Framework\PaymentGateways\Log;

use Give\Log\Log;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\ValueObjects\CardInfo;

/**
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


        foreach ($arguments[1] as $argument) {
            if ($argument instanceof GatewayPaymentData) {
                unset($argument->cardInfo, $argument->legacyPaymentData['card_info']);
            }

            if ($argument instanceof CardInfo) {
                unset($argument);
            }
        }

        parent::__callStatic($name, $arguments);
    }
}
