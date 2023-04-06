<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\StripeEventListener;
use Stripe\Checkout\Session;
use Stripe\Event;

/**
 * @since 2.21.0
 */
class CheckoutSessionCompleted extends StripeEventListener
{

    /**
     * @since 2.21.0
     *
     * @inerhitDoc
     */
    public function processEvent(Event $event)
    {
        /**
         * @since 2.26.0
         */
        do_action('give_stripe_processing_checkout_session_completed', $event);

        /* @var Session $checkoutSession */
        $checkoutSession = $event->data->object;
        $donation = $this->getDonation($event);

        if (!$donation->status->isComplete()) {
            $donation->status = DonationStatus::COMPLETE();
            $donation->gatewayTransactionId = $checkoutSession->payment_intent;
            $donation->save();
        }

        $this->addSupportForLegacyActionHook($donation->id, $event);
    }

    /**
     * @since 2.21.0
     * @return void
     */
    private function addSupportForLegacyActionHook($donationId, $event)
    {
        /**
         * This action hook will be used to extend processing the payment intent succeeded event.
         *
         * @since 2.5.5
         */
        do_action('give_stripe_process_checkout_session_completed', $donationId, $event);
    }

}
