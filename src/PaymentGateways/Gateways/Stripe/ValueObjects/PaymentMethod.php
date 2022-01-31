<?php

namespace Give\PaymentGateways\Gateways\Stripe\ValueObjects;

/**
 * @unreleased
 */
class PaymentMethod
{
    /** @var string */
    protected $paymentMethodId;

    /**
     * @unreleased
     * @param $paymentMethodId
     */
    public function __construct( $paymentMethodId )
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    /**
     * @unreleased
     * @return string
     */
    public function id()
    {
        return $this->paymentMethodId;
    }
}
