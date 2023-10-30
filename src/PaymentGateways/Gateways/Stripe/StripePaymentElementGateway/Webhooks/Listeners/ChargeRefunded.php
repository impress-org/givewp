<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Stripe\Charge;
use Stripe\Event;

/**
 * @since 3.0.0
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
     * @since 3.0.4 Add exit statement only when the event is successfully processed.
     * @since 3.0.0
     *
     * @return void
     * @throws Exception
     */
    public function __invoke(Event $event)
    {
        try {
            if ($this->processEvent($event)) {
                exit;
            }
        } catch (Exception $exception) {
            $this->logWebhookError($event, $exception);
        }
    }

    /**
     * @since 3.0.4 Return a bool value.
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function processEvent(Event $event): bool
    {
        /* @var Charge $stripeCharge */
        $stripeCharge = $event->data->object;

        $donation = give()->donations->getByGatewayTransactionId($stripeCharge->payment_intent);

        if (!$donation || !$this->shouldProcessDonation($donation)) {
            return false;
        }

        if ($stripeCharge->refunded && !$donation->status->isRefunded()) {
            $donation->status = DonationStatus::REFUNDED();
            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => __('Payment refunded in Stripe.', 'give'),
            ]);
        }

        return true;
    }
}
