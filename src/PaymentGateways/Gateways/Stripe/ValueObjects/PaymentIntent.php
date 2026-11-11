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
     * The type of additional action Stripe requires, if any.
     *
     * Stripe PaymentIntents in the `requires_action` state carry a `next_action` object whose `type`
     * determines how the donor completes the payment (for example `redirect_to_url` for 3D Secure or
     * `verify_with_microdeposits` for ACH bank account verification).
     *
     * @since TBD
     *
     * @return string|null
     */
    public function nextActionType()
    {
        return $this->paymentIntentObject->next_action->type ?? null;
    }

    /**
     * The hosted URL a donor can use to complete ACH microdeposit verification, if Stripe requires it.
     *
     * @see https://stripe.com/docs/payments/ach-direct-debit/accept-a-payment#bank-verification
     *
     * @since TBD
     *
     * @return string|null
     */
    public function nextActionVerifyWithMicrodepositsUrl()
    {
        return $this->paymentIntentObject->next_action->verify_with_microdeposits->hosted_verification_url ?? null;
    }

    /**
     * @since TBD Return null when Stripe is not redirecting (for example ACH microdeposit verification).
     * @since 2.19.0
     *
     * @return string|null
     */
    public function nextActionRedirectUrl()
    {
        return $this->paymentIntentObject->next_action->redirect_to_url->url ?? null;
    }
}
