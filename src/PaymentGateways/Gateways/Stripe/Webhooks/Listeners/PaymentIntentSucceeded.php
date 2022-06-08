<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\StripeEventListener;
use Stripe\Event;
use Stripe\PaymentIntent;

/**
 * @unreleased
 */
class PaymentIntentSucceeded extends StripeEventListener
{

    /**
     * @unreleased
     * @inerhitDoc
     */
    public function processEvent(Event $event)
    {
        /* @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;

        if (PaymentIntent::STATUS_SUCCEEDED === $paymentIntent->status) {
            $donation = $this->getDonation($event);

            if (!$donation->status->isComplete()) {
                $donation->status = DonationStatus::COMPLETE();
                $donation->save();

                give_insert_payment_note($donation->id, __('Charge succeeded in Stripe.', 'give'));
            }
        }

        $this->addSupportForLegacyActionHook($event);
    }

    /**
     * @unreleased
     */
    private function addSupportForLegacyActionHook(Event $event)
    {
        /**
         * This action hook will be used to extend processing the payment intent succeeded event.
         *
         * @since 2.5.5
         */
        do_action('give_stripe_process_payment_intent_succeeded', $event);
    }
}
