<?php

namespace Give\PaymentGateways\Gateways\Stripe\ValueObjects;

use Give_Stripe_Payment_Intent;

/**
 * @unreleased
 */
class PaymentIntent
{
    /** @var bool|\Stripe\PaymentIntent */
    protected $paymentIntentObject;

    /**
     * @unreleased
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
     * @unreleased
     * @return string
     */
    public function id()
    {
        return $this->paymentIntentObject->id;
    }

    /**
     * @unreleased
     * @return string
     */
    public function status()
    {
        return $this->paymentIntentObject->status;
    }

    /**
     * @unreleased
     * @return string
     */
    public function clientSecret()
    {
        return $this->paymentIntentObject->client_secret;
    }

    /**
     * @unreleased
     * @return string
     */
    public function nextActionRedirectUrl()
    {
        return $this->paymentIntentObject->next_action->redirect_to_url->url;
    }
}
