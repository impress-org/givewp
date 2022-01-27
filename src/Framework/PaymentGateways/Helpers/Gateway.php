<?php

namespace Give\Framework\PaymentGateways\Helpers;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\Types\OffSitePaymentGateway;

/**
 * This class provides helper methods for gateways.
 *
 * @unreleased
 */
class Gateway
{
    /**
     * Return whether payment gateway is offsite type.
     *
     * @unreleased
     *
     * @return bool
     */
    public static function isOffsitePaymentGateway( PaymentGatewayInterface $paymentGateway )
    {
        return $paymentGateway instanceof OffSitePaymentGateway;
    }
}
