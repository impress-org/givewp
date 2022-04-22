<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\Models\Donation;
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

        $donationId = give_get_purchase_id_by_transaction_id($checkoutSession->id);

        if (!$donationId) {
            return;
        }

        $donationModel = Donation::find($donationId);
        $donationModel->status = DonationStatus::COMPLETE();
        $donationModel->gatewayTransactionId = $checkoutSession->payment_intent;
        $donationModel->save();

        // Insert donation note to inform admin that charge succeeded.
        give_insert_payment_note(
            $donationModel->id,
            esc_html__('Charge succeeded in Stripe.', 'give')
        );

        $this->addSupportForLegacyActionHook($donationId, $event);
    }

    /**
     * @unreleased
     *
     * @return int
     */
    public function getFormId(Event $event)
    {
        return give_get_purchase_id_by_transaction_id($event->data->object->id);
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
