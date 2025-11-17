<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Actions\UpdateStripeInvoiceMetaData;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Decorators\SubscriptionModelDecorator;
use Give\Subscriptions\Models\Subscription;
use Stripe\Event;
use Stripe\Invoice;

/**
 * @since 3.0.0
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
     * @since 3.0.4 Add exit statement only when the event is successfully processed.
     * @since 3.0.0
     *
     * @throws \Exception
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
     * @since 4.8.0 Add support for Stripe API version 2025-03-31.basil and later versions
     * @since 4.3.0 Update Stripe Invoice metadata
     * @since 3.0.4 Return a bool value.
     * @since 3.0.0
     * @throws \Exception
     */
    public function processEvent(Event $event): bool
    {
        /* @var Invoice $invoice */
        $invoice = $event->data->object;

        $gatewaySubscriptionId = $this->getGatewaySubscriptionId($invoice);

        $subscription = give()->subscriptions->queryByGatewaySubscriptionId($gatewaySubscriptionId)->get();

        // only use this for next gen for now
        if (!$subscription || !$this->shouldProcessSubscription($subscription)) {
            return false;
        }

        /**
         * This checking is necessary because the invoice data returned in webhook events
         * can be incomplete and may not include the payment_intent property, especially
         * with newer Stripe API versions like 2025-03-31.basil. By making a direct
         * API call to retrieve the invoice, we ensure we get all properties including
         * the payment_intent which is required for processing this webhook.
         */
        if (is_null($invoice->payment_intent)) {
            $invoice = $this->getCompleteInvoiceFromStripe($event->data->object->id);
        }

        $gatewayTransactionId = $invoice->payment_intent;
        $initialDonation = give()->donations->getByGatewayTransactionId($gatewayTransactionId);
        if ($initialDonation) {
            $this->handleInitialDonation($initialDonation);
            $this->updateStripeInvoiceMetaData($invoice, $initialDonation);
        } else {
            $subscriptionModel = $this->getSubscriptionModelDecorator($subscription);

            if ($subscriptionModel->shouldCreateRenewal()) {
                $subscriptionModel = $subscriptionModel->handleRenewal($invoice);
                $renewalDonation = $subscriptionModel->subscription->donations[0];
                $this->updateStripeInvoiceMetaData($invoice, $renewalDonation);
            }

            if ($subscriptionModel->shouldEndSubscription()) {
                $this->cancelSubscription($subscriptionModel);
                $subscriptionModel->handleSubscriptionCompleted();
            }
        }

        return true;
    }

    /**
     * @since 3.0.0
     */
    protected function getSubscriptionModelDecorator(Subscription $subscription): SubscriptionModelDecorator
    {
        return new SubscriptionModelDecorator($subscription);
    }

    /**
     * @since 3.0.0
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
     * @since 3.0.0
     * @throws \Exception
     */
    protected function cancelSubscription(SubscriptionModelDecorator $subscriptionModel)
    {
        $subscriptionModel->cancelSubscription();
    }

    /**
     * @since 4.3.0
     */
    protected function updateStripeInvoiceMetaData(Invoice $invoice, Donation $donation)
    {
        give(UpdateStripeInvoiceMetaData::class)($invoice, $donation);
    }
}
