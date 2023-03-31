<?php

namespace Give\NextGen\Gateways\Stripe\NextGenStripeGateway\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Stripe\Charge;
use Stripe\Event;

/**
 * @unreleased
 */
class ChargeRefunded
{
    use StripeWebhookListenerRepository;

    /**
     * Processes charge.refunded event.
     *
     * Occurs whenever a charge is refunded, including partial refunds.
     *
     * @see https://stripe.com/docs/api/events/types#event_types-charge.refunded
     *
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function __invoke(Event $event)
    {
        try {
            $this->processEvent($event);
        } catch (Exception $exception) {
            $this->logWebhookError($event, $exception);
        }

        exit;
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function processEvent(Event $event)
    {
        /* @var Charge $stripeCharge */
        $stripeCharge = $event->data->object;

        $donation = give()->donations->getByGatewayTransactionId($stripeCharge->payment_intent);

        if (!$donation || !$this->shouldProcessDonation($donation)) {
            return;
        }

        if ($stripeCharge->refunded && !$donation->status->isRefunded()) {
            $donation->status = DonationStatus::REFUNDED();
            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => __('Payment refunded in Stripe.', 'give'),
            ]);
        }
    }
}
