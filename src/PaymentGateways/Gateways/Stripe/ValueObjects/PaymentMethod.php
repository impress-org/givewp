<?php

namespace Give\PaymentGateways\Gateways\Stripe\ValueObjects;

class PaymentMethod
{
    /** @var string */
    protected $paymentMethodId;

    public function __construct( $paymentMethodId )
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    public function id()
    {
        return $this->paymentMethodId;
    }
}
