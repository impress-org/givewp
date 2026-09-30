<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\PaymentGateways\Gateways\Stripe\Actions\RecordMicrodepositVerification;
use Stripe\Event;
use Stripe\PaymentIntent;

/**
 * Handles the `payment_intent.requires_action` Stripe webhook event when the required action is ACH
 * microdeposit verification.
 *
 * The Stripe Payment Element confirms the PaymentIntent on the client, so the server only learns about
 * a `verify_with_microdeposits` next action from this webhook. The gateway status handler covers the
 * server-confirmed gateways; this listener covers the Payment Element.
 *
 * @since TBD
 */
class PaymentIntentRequiresAction
{
    /**
     * @since TBD
     *
     * @return void
     */
    public function __invoke(Event $event)
    {
        /* @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;

        if ('verify_with_microdeposits' !== ($paymentIntent->next_action->type ?? null)) {
            return;
        }

        $hostedVerificationUrl = $paymentIntent->next_action->verify_with_microdeposits->hosted_verification_url ?? null;

        if (empty($hostedVerificationUrl)) {
            return;
        }

        $donation = give()->donations->getByGatewayTransactionId($paymentIntent->id);

        if ( ! $donation) {
            return;
        }

        give(RecordMicrodepositVerification::class)($donation, $hostedVerificationUrl);
    }
}
