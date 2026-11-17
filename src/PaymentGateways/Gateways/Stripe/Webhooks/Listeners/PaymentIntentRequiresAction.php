<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\Models\Donation;
use Give\PaymentGateways\Gateways\Stripe\Actions\RecordMicrodepositVerification;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\StripeEventListener;
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
 * The parent class re-fetches the event from the Stripe account of the donation's form, so the hosted
 * verification URL emailed to the donor never comes from the unauthenticated webhook request body.
 *
 * @since 4.19.2
 */
class PaymentIntentRequiresAction extends StripeEventListener
{
    /**
     * @since 4.19.2
     *
     * @return void
     */
    public function processEvent(Event $event)
    {
        /* @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;

        if ('verify_with_microdeposits' !== ($paymentIntent->next_action->type ?? null)) {
            return;
        }

        $hostedVerificationUrl = $paymentIntent->next_action->verify_with_microdeposits->hosted_verification_url ?? null;

        if ( ! $hostedVerificationUrl) {
            return;
        }

        $donation = $this->getDonation($event);

        if ( ! $donation || ! $this->matchesDonationAmount($paymentIntent, $donation)) {
            return;
        }

        give(RecordMicrodepositVerification::class)($donation, $hostedVerificationUrl);
    }

    /**
     * The gateways create the PaymentIntent from the donation amount, so any difference means the
     * PaymentIntent is not the one this donation was charged with.
     *
     * @since 4.19.2
     *
     * @param PaymentIntent $paymentIntent The PaymentIntent from the Stripe event.
     * @param Donation      $donation      The donation linked to the PaymentIntent ID.
     *
     * @return bool Whether the PaymentIntent amount and currency equal the donation's.
     */
    private function matchesDonationAmount(PaymentIntent $paymentIntent, Donation $donation): bool
    {
        return (int)$paymentIntent->amount === (int)$donation->amount->formatToMinorAmount()
            && strtoupper((string)$paymentIntent->currency) === $donation->amount->getCurrency()->getCode();
    }
}
