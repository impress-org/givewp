<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateDonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateSubscriptionStatus;
use Give\Log\Log;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
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
     * For Stripe Connect accounts, we need to pass the connected account ID to retrieve the invoice from the correct account, not the platform account.
     *
     * @since 4.20.0 Read the request options from getStripeRequestOptions().
     * @since 4.19.1 Escape exception message.
     * @since 4.15.3 Added support for using a secret key when retrieving invoices for non-default accounts
     * 4.14.1 Added $formId parameter and stripe_account option for Stripe Connect support
     *
     * @param string $invoiceId The Stripe invoice ID
     * @param int|null $formId Form ID to determine the connected account
     * @return Invoice The complete Stripe invoice object with all properties
     * @throws Exception If the API call fails
     */
    protected function getCompleteInvoiceFromStripe(string $invoiceId, ?int $formId = null): Invoice
    {
        try {
            return \Stripe\Invoice::retrieve($invoiceId, $this->getStripeRequestOptions($formId));
        } catch (ApiErrorException $exception) {
            throw new Exception(
                sprintf(
                    'Failed to retrieve invoice %s: %s',
                    esc_html($invoiceId),
                    $exception->getMessage() // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- caught by InvoicePaymentSucceeded/InvoicePaymentFailed and only logged via Log::error(), never displayed.
                )
            );
        }
    }

    /**
     * Mark the first donation of a subscription as failed, and move a subscription that was set to
     * active before its payment was confirmed back to pending.
     *
     * Only a pending or processing donation is changed, so a redelivered event adds no second note.
     * A subscription that is still active is moved to pending even when its first donation was
     * already failed.
     *
     * @since 4.20.0
     *
     * @param Donation $initialDonation The first donation of the subscription
     *
     * @throws Exception
     */
    protected function handleInitialSubscriptionPaymentFailed(Donation $initialDonation): void
    {
        if ($initialDonation->status->isPending() || $initialDonation->status->isProcessing()) {
            (new UpdateDonationStatus())(
                $initialDonation,
                DonationStatus::FAILED(),
                __('The first subscription payment failed in Stripe.', 'give')
            );
        }

        if (!$initialDonation->status->isFailed()) {
            return;
        }

        $subscription = $initialDonation->subscription;

        if ($subscription && $subscription->status->isActive()) {
            (new UpdateSubscriptionStatus())(
                $subscription,
                SubscriptionStatus::PENDING(),
                __('The subscription was moved to pending because its first payment failed in Stripe.', 'give')
            );
        }
    }

    /**
     * Build the options for a Stripe API request made with the account the form uses.
     *
     * @since 4.20.0
     *
     * @param int|null $formId Form ID to determine the connected account
     *
     * @return array{stripe_account?: string, api_key?: string} The form's connected account and secret key, if set
     */
    protected function getStripeRequestOptions(?int $formId = null): array
    {
        $options = [];

        if ($formId) {
            $connectedAccountId = give_stripe_get_connected_account_id($formId);
            if ($connectedAccountId) {
                $options = ['stripe_account' => $connectedAccountId];
            }

            $secretKey = give_stripe_get_secret_key($formId);
            if ($secretKey) {
                $options['api_key'] = $secretKey;
            }
        }

        return $options;
    }
}
