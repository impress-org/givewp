<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
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
     * @since 3.0.0
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
     * @since 3.0.0
     *
     * @throws Exception
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

        if (
            $invoice->attempted &&
            !$invoice->paid &&
            null !== $invoice->next_payment_attempt
        ) {
            $this->triggerLegacyFailedEmailNotificationEvent($invoice);

            $subscription->status = SubscriptionStatus::FAILING();
            $subscription->save();
        }
    }

    /**
     * @since 3.0.0
     */
    protected function triggerLegacyFailedEmailNotificationEvent(Invoice $invoice)
    {
        // @phpstan-ignore-next-line
        $subscription = give_recurring_get_subscription_by('profile', $invoice->subscription);

        do_action('give_donor-subscription-payment-failed_email_notification', $subscription, $invoice);

        // Log the invoice object for debugging purpose.
        give_stripe_record_log(
            esc_html__('Subscription - Renewal Payment Failed', 'give'),
            print_r($invoice, true)
        );
    }
}
