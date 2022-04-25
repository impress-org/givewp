<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\StripeEventListener;
use http\Exception\InvalidArgumentException;
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
        $donation->status = DonationStatus::COMPLETE();
        $donation->gatewayTransactionId = $checkoutSession->payment_intent;
        $donation->save();

        // Insert donation note to inform admin that charge succeeded.
        give_insert_payment_note(
            $donation->id,
            esc_html__('Charge succeeded in Stripe.', 'give')
        );

        $this->addSupportForLegacyActionHook($donation->id, $event);
    }

    /**
     * @unreleased
     *
     * @return int
     */
    public function getFormId(Event $event)
    {
        return $this->getDonation($event)->formId;
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

    /**
     * @unreleased
     *
     * @inerhitDoc
     */
    public function getDonation(Event $event)
    {
        if ($donation = Donation::findByGatewayTransactionId($event->data->object->id)) {
            return $donation;
        }

        throw new InvalidArgumentException('Unable to find donation for the Stripe event.');
    }
}
