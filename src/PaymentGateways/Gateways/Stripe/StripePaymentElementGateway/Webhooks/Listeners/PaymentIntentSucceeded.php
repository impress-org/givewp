<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Stripe\Event;
use Stripe\PaymentIntent;

/**
 * @since 3.0.0
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
     */
    public function processEvent(Event $event): bool
    {
        /* @var PaymentIntent $paymentIntent */
        $paymentIntent = $event->data->object;

        $donation = give()->donations->getByGatewayTransactionId($paymentIntent->id);

        if (!$donation || !$this->shouldProcessDonation($donation)) {
            return false;
        }

        if ($donation->type->isSingle() && !$donation->status->isComplete()) {
            $donation->status = DonationStatus::COMPLETE();
            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => __('Payment succeeded in Stripe.', 'give'),
            ]);
        }

        return true;
    }
}
