<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Log\Log;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\Subscriptions\Models\Subscription;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;

trait StripeWebhookListenerRepository
{
    /**
     * @since 3.0.0
     */
    protected function shouldProcessSubscription(Subscription $subscription): bool
    {
        return $subscription->gatewayId === StripePaymentElementGateway::id();
    }

    /**
     * @since 3.0.0
     */
    protected function shouldProcessDonation(Donation $donation): bool
    {
        return $donation->gatewayId === StripePaymentElementGateway::id();
    }

    /**
     * @since 3.0.0
     */
    protected function logWebhookError(Event $event, Exception $exception)
    {
        Log::error(
            sprintf(
                'Stripe Webhook Error: %s',
                $event->type
            ),
            [
                'event' => $event,
                'exception' => $exception->getMessage(),
            ]
        );
    }

    /**
     * Get the gateway subscription ID from an invoice, supporting both old and new Stripe API versions.
     *
     * This method handles the transition from the legacy API where subscription ID was directly
     * accessible via `$invoice->subscription` to the new Basil API (2025-03-31.basil and later versions)
     * where subscription data is nested under `$invoice->parent->subscription_details->subscription`.
     *
     * The Basil API introduced breaking changes that moved subscription-related fields from
     * the top level of invoice objects to a new `parent` field structure. This change affects
     * how we access subscription IDs, subscription details, and other related properties.
     *
     * @see https://docs.stripe.com/changelog/basil/2025-03-31/adds-new-parent-field-to-invoicing-objects
     *
     * @since 4.8.0
     *
     * @param Invoice $invoice The Stripe invoice object
     * @return string|null The gateway subscription ID or null if not found
     */
    protected function getGatewaySubscriptionId(Invoice $invoice): ?string
    {
        // Try new Basil API structure first (parent field)
        if (isset($invoice->parent) &&
            isset($invoice->parent->subscription_details) &&
            isset($invoice->parent->subscription_details->subscription)) {
            return $invoice->parent->subscription_details->subscription;
        }

        // Fallback to old API structure (direct subscription field)
        if (isset($invoice->subscription)) {
            return $invoice->subscription;
        }

        return null;
    }

    /**
     * Retrieve a complete invoice from the Stripe API.
     *
     * @since 4.8.0
     *
     * @param string $invoiceId The Stripe invoice ID
     * @return Invoice The complete Stripe invoice object with all properties
     * @throws Exception If the API call fails
     */
    protected function getCompleteInvoiceFromStripe(string $invoiceId): Invoice
    {
        try {
            return \Stripe\Invoice::retrieve($invoiceId);
        } catch (ApiErrorException $exception) {
            throw new Exception(
                sprintf(
                    'Failed to retrieve invoice %s: %s',
                    $invoiceId,
                    $exception->getMessage()
                )
            );
        }
    }
}
