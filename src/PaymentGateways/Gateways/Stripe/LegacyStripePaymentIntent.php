<?php

namespace Give\PaymentGateways\Gateways\Stripe;

use Give_Stripe_Payment_Intent;

class LegacyStripePaymentIntent
{
    /** @var bool|\Stripe\PaymentIntent */
    protected $paymentIntentObject;

    /**
     * @param $paymentIntentArgs
     * @return LegacyStripePaymentIntent
     */
    public function create( $paymentIntentArgs )
    {
        $paymentIntent = new Give_Stripe_Payment_Intent;
        $this->paymentIntentObject = $paymentIntent->create( $paymentIntentArgs );
        return $this;
    }

    public function id()
    {
        return $this->paymentIntentObject->id;
    }

    public function status()
    {
        return $this->paymentIntentObject->status;
    }

    public function nextActionRedirectUrl()
    {
        return $this->paymentIntentObject->next_action->redirect_to_url->url;
    }
}
