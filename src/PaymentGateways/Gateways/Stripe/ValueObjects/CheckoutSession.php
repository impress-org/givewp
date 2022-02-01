<?php

namespace Give\PaymentGateways\Gateways\Stripe\ValueObjects;

use Give_Stripe_Checkout_Session;

/**
 * @unreleased
 */
class CheckoutSession
{
    /** @var bool|\Stripe\PaymentIntent */
    protected $checkoutSessionObject;

    /**
     * @unreleased
     * @param $paymentIntentArgs
     * @return CheckoutSession
     */
    public function create( $paymentIntentArgs )
    {
        $this->checkoutSessionObject = give( Give_Stripe_Checkout_Session::class )->create( $paymentIntentArgs );
        return $this;
    }

    /**
     * @unreleased
     * @return string
     */
    public function id()
    {
        return $this->checkoutSessionObject->id;
    }

    /**
     * @unreleased
     * @return string
     */
    public function url()
    {
        return $this->checkoutSessionObject->url;
    }
}
