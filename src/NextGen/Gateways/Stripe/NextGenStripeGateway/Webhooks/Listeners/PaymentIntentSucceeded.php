<?php

namespace Give\NextGen\Gateways\Stripe\NextGenStripeGateway\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Stripe\Event;
use Stripe\PaymentIntent;

/**
 * @unreleased
 */
class PaymentIntentSucceeded
{
    use StripeWebhookListenerRepository;

    /**
     * Processes invoice.payment_succeeded event.
     *
     * Occurs whenever an invoice payment attempt succeeds.
     *
     * @see https://stripe.com/docs/api/events/types#event_types-invoice.payment_succeeded
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
     */
    public function processEvent(Event $event)
    {
        /* @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;

        $donation = give()->donations->getByGatewayTransactionId($paymentIntent->id);

        if (!$donation || !$this->shouldProcessDonation($donation)) {
            return;
        }

        if ($donation->type->isSingle() && !$donation->status->isComplete()) {
            $donation->status = DonationStatus::COMPLETE();
            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => __('Payment succeeded in Stripe.', 'give'),
            ]);
        }
    }
}
