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
     * Retrieve a complete invoice from the Stripe API.
     *
     * This method is necessary because the invoice data returned in webhook events
     * may be incomplete and missing required properties like payment_intent or subscription,
     * especially with newer Stripe API versions like 2025-03-31.basil. By making a direct
     * API call to retrieve the invoice, we ensure we get all properties including
     * the payment_intent and subscription which are required for processing the webhook.
     *
     * @see https://docs.stripe.com/changelog/basil/2025-03-31/adds-new-parent-field-to-invoicing-objects
     *
     * @since @unreleased
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

    /**
     * Get invoice from webhook event with fallback to API call if needed.
     *
     * This method intelligently checks if the webhook event contains all required
     * invoice properties (subscription and payment_intent). If they are present,
     * it uses the webhook data directly. Otherwise, it makes an API call to
     * retrieve the complete invoice data.
     *
     * @since @unreleased
     *
     * @param Event $event The Stripe webhook event
     * @return Invoice The complete Stripe invoice object with all required properties
     * @throws Exception If the API call fails
     */
    protected function getInvoiceFromEvent(Event $event): Invoice
    {
        $webhookInvoice = $event->data->object;

        // Check if required fields are available in the webhook data
        $hasRequiredFields = isset($webhookInvoice->subscription) && isset($webhookInvoice->payment_intent);

        if ($hasRequiredFields) {
            // Use webhook data directly if all required fields are present
            return $webhookInvoice;
        }

        // Get complete invoice from Stripe API to ensure all properties are available
        return $this->getCompleteInvoiceFromStripe($webhookInvoice->id);
    }
}
