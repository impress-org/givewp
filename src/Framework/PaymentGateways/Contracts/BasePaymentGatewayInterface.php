<?php

namespace Give\Framework\PaymentGateways\Contracts;

/**
 * @unreleased
 */
interface BasePaymentGatewayInterface
{
    /**
     * Return a unique identifier for the gateway
     *
     * @since 2.18.0
     */
    public static function id(): string;

}