<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Stripe\Event;
use Stripe\Invoice;

/**
 * @since 3.0.0
 */
class InvoicePaymentFailed
{
    use StripeWebhookListenerRepository;

    /**
     * Processes invoice.payment_failed event.
     *
     * Occurs whenever an invoice payment attempt fails, due either to a declined payment or to the lack of a stored payment method.
     *
     * @see https://stripe.com/docs/api/events/types#event_types-invoice.payment_failed
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
     * @since TBD Fail the first donation when the invoice that creates the subscription is not paid.
     * @since 4.8.0 Add support for Stripe API version 2025-03-31.basil and later versions
     * @since 3.0.4 Return a bool value.
     * @since 3.0.0
     *
     * @throws Exception
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

        /*
         * Stripe does not retry the first invoice of a subscription created with
         * payment_behavior=default_incomplete, so next_payment_attempt is null and the renewal
         * branch below never matches it.
         */
        if ($invoice->billing_reason === 'subscription_create') {
            $this->handleFirstInvoicePaymentFailed($subscription, $invoice);

            return true;
        }

        if (
            $invoice->attempted &&
            !$invoice->paid &&
            null !== $invoice->next_payment_attempt
        ) {
            $this->triggerLegacyFailedEmailNotificationEvent($invoice);

            $subscription->status = SubscriptionStatus::FAILING();
            $subscription->save();
        }

        return true;
    }

    /**
     * Fail the first donation of a subscription whose first invoice was not paid.
     *
     * The invoice is fetched from Stripe first, because the payload the Give Recurring add-on passes
     * on is not re-fetched.
     *
     * @since TBD
     *
     * @param Subscription $subscription The GiveWP subscription the invoice belongs to
     * @param Invoice $invoice The invoice from the webhook payload
     *
     * @throws Exception
     */
    protected function handleFirstInvoicePaymentFailed(Subscription $subscription, Invoice $invoice): void
    {
        $invoice = $this->getCompleteInvoiceFromStripe($invoice->id, $subscription->donationFormId);

        if ($invoice->billing_reason !== 'subscription_create' || $invoice->status === 'paid') {
            return;
        }

        $initialDonation = $subscription->initialDonation();

        if ($initialDonation) {
            $this->handleInitialSubscriptionPaymentFailed($initialDonation);
        }
    }

    /**
     * @since 4.19.1 Write the invoice to the log as JSON.
     * @since 4.8.0 Add support for Stripe API version 2025-03-31.basil and later versions
     * @since 3.0.0
     */
    protected function triggerLegacyFailedEmailNotificationEvent(Invoice $invoice)
    {
        $gatewaySubscriptionId = $this->getGatewaySubscriptionId($invoice);

        // @phpstan-ignore-next-line
        $subscription = give_recurring_get_subscription_by('profile', $gatewaySubscriptionId);

        do_action('give_donor-subscription-payment-failed_email_notification', $subscription, $invoice);

        // Log the invoice object for debugging purpose.
        give_stripe_record_log(
            esc_html__('Subscription - Renewal Payment Failed', 'give'),
            wp_json_encode($invoice, JSON_PRETTY_PRINT)
        );
    }
}
