<?php

namespace Give\PaymentGateways\Gateways\Stripe\Traits;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\PaymentCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentIntentException;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;

trait HandlePaymentIntentStatus
{
    /**
     * @since 2.21.0 Update second argument type to Donation model
     * @since 2.19.7 fix param order and only pass donationId
     *
     * @return PaymentCommand|RedirectOffsite
     * @throws PaymentIntentException
     */
    public function handlePaymentIntentStatus(PaymentIntent $paymentIntent, Donation $donation)
    {
        switch ($paymentIntent->status()) {
            case 'requires_action':
                $donation->gatewayTransactionId = $paymentIntent->id();
                $donation->save();
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
