<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\StripeEventListener;
use Stripe\Checkout\Session;
use Stripe\Event;

/**
 * @unreleased
 */
class CheckoutSessionCompleted extends StripeEventListener
{

    /**
     * @unreleased
     *
     * @inerhitDoc
     */
    public function processEvent(Event $event)
    {
        /* @var Session $checkoutSession */
        $checkoutSession = $event->data->object;
        $donation = $this->getDonation($event);

        if (!$donation->status->isComplete()) {
            $donation->status = DonationStatus::COMPLETE();
            $donation->gatewayTransactionId = $checkoutSession->payment_intent;
            $donation->save();

            // Insert donation note to inform admin that charge succeeded.
            give_insert_payment_note(
                $donation->id,
                esc_html__('Charge succeeded in Stripe.', 'give')
            );
        }

        $this->addSupportForLegacyActionHook($donation->id, $event);
    }

    /**
     * @unreleased
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
