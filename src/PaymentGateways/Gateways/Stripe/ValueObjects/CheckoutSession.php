<?php

namespace Give\PaymentGateways\Gateways\Stripe\ValueObjects;

use Give_Stripe_Checkout_Session;

/**
 * @since 2.19.0
 */
class CheckoutSession
{
    /** @var bool|\Stripe\PaymentIntent */
    protected $checkoutSessionObject;

    /**
     * @since 2.19.0
     * @param $paymentIntentArgs
     * @return CheckoutSession
     */
    public function create( $paymentIntentArgs )
    {
        $this->checkoutSessionObject = give( Give_Stripe_Checkout_Session::class )->create( $paymentIntentArgs );
        return $this;
    }

    /**
     * @since 2.19.0
     * @return string
     */
    public function id()
    {
        return $this->checkoutSessionObject->id;
    }

    /**
     * @since 2.19.0
     * @return string
     */
    public function url()
    {
        return $this->checkoutSessionObject->url;
    }
}
