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
class PaymentIntentPaymentFailed
{
    use StripeWebhookListenerRepository;

    /**
     * Processes payment_intent.payment_failed event.
     *
     * Occurs when a PaymentIntent has failed the attempt to create a payment method or a payment.
     *
     * @see https://stripe.com/docs/api/events/types#event_types-payment_intent.payment_failed
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

        if ($donation->type->isSingle() && !$donation->status->isFailed()) {
            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => __('Payment failed in Stripe.', 'give'),
            ]);
        }

        return true;
    }
}
