<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Decorators\SubscriptionModelDecorator;
use Give\Subscriptions\Models\Subscription;
use Stripe\Event;
use Stripe\Invoice;

/**
 * @since 0.3.0
 */
class InvoicePaymentSucceeded
{
    use StripeWebhookListenerRepository;

    /**
     * Processes invoice.payment_succeeded event.
     *
     * Occurs whenever an invoice payment attempt succeeds.
     *
     * @see https://stripe.com/docs/api/events/types#event_types-invoice.payment_succeeded
     *
     * @since 0.3.0
     *
     * @return void
     * @throws \Exception
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
     * @since 0.3.0
     * @throws \Exception
     */
    public function processEvent(Event $event)
    {
        /* @var Invoice $invoice */
        $invoice = $event->data->object;

        $subscription = give()->subscriptions->queryByGatewaySubscriptionId($invoice->subscription)->get();

        // only use this for next gen for now
        if (!$subscription || !$this->shouldProcessSubscription($subscription)) {
            return;
        }

        if ($initialDonation = give()->donations->getByGatewayTransactionId($invoice->payment_intent)) {
            $this->handleInitialDonation($initialDonation);
        } else {
            $subscriptionModel = $this->getSubscriptionModelDecorator($subscription);

            if ($subscriptionModel->shouldCreateRenewal()) {
                $subscriptionModel = $subscriptionModel->handleRenewal($invoice);
            }

            if ($subscriptionModel->shouldEndSubscription()) {
                $this->cancelSubscription($subscriptionModel);
                $subscriptionModel->handleSubscriptionCompleted();
            }
        }
    }

    /**
     * @since 0.3.0
     */
    protected function getSubscriptionModelDecorator(Subscription $subscription): SubscriptionModelDecorator
    {
        return new SubscriptionModelDecorator($subscription);
    }


    /**
     * @since 0.3.0
     *
     * @throws Exception
     */
    protected function handleInitialDonation(Donation $initialDonation)
    {
        // update initial donation
        // TODO: the payment_intent.succeeded event has this same logic
        if (!$initialDonation->status->isComplete()) {
            $initialDonation->status = DonationStatus::COMPLETE();
            $initialDonation->save();

            DonationNote::create([
                'donationId' => $initialDonation->id,
                'content' => __('Payment succeeded in Stripe.', 'give'),
            ]);
        }
    }

    /**
     * @since 0.3.0
     * @throws \Exception
     */
    protected function cancelSubscription(SubscriptionModelDecorator $subscriptionModel)
    {
        $subscriptionModel->cancelSubscription();
    }
}
