<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

use Give\Framework\PaymentGateways\Commands\PaymentCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentIntentException;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;

trait HandlePaymentIntentStatus
{
    /**
     * @since 2.19.7 fix param order and only pass donationId
     *
     * @param PaymentIntent $paymentIntent
     * @param string $donationId
     *
     * @return PaymentCommand|RedirectOffsite
     * @throws PaymentIntentException
     */
    public function handlePaymentIntentStatus(PaymentIntent $paymentIntent, $donationId)
    {
        switch ($paymentIntent->status()) {
            case 'requires_action':
                give_set_payment_transaction_id($donationId, $paymentIntent->id());
                return new RedirectOffsite($paymentIntent->nextActionRedirectUrl());
            case 'succeeded':
                return new PaymentComplete($paymentIntent->id());
            case 'processing':
                return new PaymentProcessing($paymentIntent->id());
            default:
                throw new PaymentIntentException(
                    sprintf(__('Unhandled payment intent status: %s', 'give'), $paymentIntent->status())
                );
        }
    }
}
