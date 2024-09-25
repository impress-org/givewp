<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\StripeEventListener;
use Stripe\Charge;
use Stripe\Event;

/**
 * @since 2.21.0
 */
class ChargeRefunded extends StripeEventListener
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
        do_action('give_stripe_processing_charge_refunded', $event);

        /* @var Charge $stripeCharge */
        $stripeCharge = $event->data->object;
        $donation = $this->getDonation($event);

        if ($stripeCharge->refunded && !$donation->status->isRefunded()) {
            $donation->status = DonationStatus::REFUNDED();
            $donation->save();

            give_insert_payment_note($donation->id, __('Charge refunded in Stripe.', 'give'));
        }

        // TODO handle stripe partial refund.

        $this->addSupportForLegacyActionHook($event);
    }

    /**
     * @since 2.21.0
     */
    private function addSupportForLegacyActionHook(Event $event)
    {
        /**
         * This action hook will be used to extend processing the charge refunded event.
         *
         * @since 2.5.5
         */
        do_action('give_stripe_process_charge_refunded', $event);
    }

    /**
     * @since 2.21.0
     * @inerhitDoc
     */
    protected function getDonation(Event $event)
    {
        /* @var Charge $stripeCharge */
        $stripeCharge = $event->data->object;

        return give(DonationRepository::class)->getByGatewayTransactionId($stripeCharge->id);
    }
}
