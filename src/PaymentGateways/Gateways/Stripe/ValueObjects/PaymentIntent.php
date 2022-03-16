<?php

namespace Give\PaymentGateways\Gateways\Stripe\ValueObjects;

use Give_Stripe_Payment_Intent;

/**
 * @since 2.19.0
 */
class PaymentIntent
{
    /** @var bool|\Stripe\PaymentIntent */
    protected $paymentIntentObject;

    /**
     * @since 2.19.0
     * @param $paymentIntentArgs
     * @return PaymentIntent
     */
    public function create( $paymentIntentArgs )
    {
        $paymentIntentFactory = give( Give_Stripe_Payment_Intent::class );
        $this->paymentIntentObject = $paymentIntentFactory->create( $paymentIntentArgs );
        return $this;
    }

    /**
     * @since 2.19.0
     * @return string
     */
    public function id()
    {
        return $this->paymentIntentObject->id;
    }

    /**
     * @since 2.19.0
     * @return string
     */
    public function status()
    {
        return $this->paymentIntentObject->status;
    }

    /**
     * @since 2.19.0
     * @return string
     */
    public function clientSecret()
    {
        return $this->paymentIntentObject->client_secret;
    }

    /**
     * @since 2.19.0
     * @return string
     */
    public function nextActionRedirectUrl()
    {
        return $this->paymentIntentObject->next_action->redirect_to_url->url;
    }
}
