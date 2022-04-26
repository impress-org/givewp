<?php

namespace Give\PaymentGateways\Gateways\Stripe\Webhooks\Listeners;

use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\PaymentGateways\Gateways\Stripe\Webhooks\StripeEventListener;
use Stripe\Charge;
use Stripe\Event;

/**
 * @unreleased
 */
class ChargeRefunded extends StripeEventListener
{
    /**
     * @unreleased
     *
     * @inerhitDoc
     */
    public function processEvent(Event $event)
    {
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
     * @unreleased
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
     * @unreleased
     * @inerhitDoc
     */
    protected function getDonation(Event $event)
    {
        /* @var Charge $stripeCharge */
        $stripeCharge = $event->data->object;

        if ($donation = give(DonationRepository::class)->getByGatewayTransactionId($stripeCharge->payment_intent)) {
            return $donation;
        }

        throw new InvalidArgumentException('Unable to find donation for the Stripe event.');
    }
}
