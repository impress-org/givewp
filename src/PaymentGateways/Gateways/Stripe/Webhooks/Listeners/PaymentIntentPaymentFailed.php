<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\StripeEventListener;
use Stripe\Event;

class PaymentIntentPaymentFailed extends StripeEventListener
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
        do_action('give_stripe_processing_payment_intent_failed', $event);

        $donation = $this->getDonation($event);

        if (!$donation->status->isFailed()) {
            $donation->status = DonationStatus::FAILED();
            $donation->save();

            give_insert_payment_note($donation->id, __('Charge failed in Stripe.', 'give'));
        }

        $this->addSupportForLegacyActionHook($event);
    }

    /**
     * @since 2.21.0
     */
    private function addSupportForLegacyActionHook(Event $event)
    {
        /**
         * This action hook will be used to extend processing the payment intent failed event.
         *
         * @since 2.5.5
         */
        do_action('give_stripe_process_payment_intent_failed', $event);
    }
}
