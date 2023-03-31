<?php

namespace Give\NextGen\Gateways\Stripe\NextGenStripeGateway\Webhooks\Listeners;

use Exception;
use Give\Donations\Models\Donation;
use Give\Log\Log;
use Give\NextGen\Gateways\Stripe\NextGenStripeGateway\NextGenStripeGateway;
use Give\Subscriptions\Models\Subscription;
use Stripe\Event;

trait StripeWebhookListenerRepository
{
    /**
     * @unreleased
     */
    protected function shouldProcessSubscription(Subscription $subscription): bool
    {
        return $subscription->gatewayId === NextGenStripeGateway::id();
    }

    /**
     * @unreleased
     */
    protected function shouldProcessDonation(Donation $donation): bool
    {
        return $donation->gatewayId === NextGenStripeGateway::id();
    }

    /**
     * @unreleased
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
}