<?php

namespace Give\PaymentGateways\Gateways\Stripe\ValueObjects;

/**
 * @since 2.19.0
 */
class PaymentMethod
{
    /** @var string */
    protected $paymentMethodId;

    /**
     * @since 2.19.0
     * @param $paymentMethodId
     */
    public function __construct( $paymentMethodId )
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    /**
     * @since 2.19.0
     * @return string
     */
    public function id(): string
    {
        return $this->paymentMethodId;
    }
}
