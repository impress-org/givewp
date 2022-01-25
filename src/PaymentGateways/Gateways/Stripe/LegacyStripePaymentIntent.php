<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give_Stripe_Payment_Intent;

/**
 * @unreleased
 */
class LegacyStripePaymentIntent
{
    /** @var bool|\Stripe\PaymentIntent */
    protected $paymentIntentObject;

    /**
     * @unreleased
     * @param $paymentIntentArgs
     * @return LegacyStripePaymentIntent
     */
    public function create( $paymentIntentArgs )
    {
        $paymentIntent = new Give_Stripe_Payment_Intent;
        $this->paymentIntentObject = $paymentIntent->create( $paymentIntentArgs );
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
    public function nextActionRedirectUrl()
    {
        return $this->paymentIntentObject->next_action->redirect_to_url->url;
    }
}
