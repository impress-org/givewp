<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class SubscriptionSuspended
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(string $gatewaySubscriptionId, string $message = '')
    {
        $subscription = give()->subscriptions->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ( ! $subscription || $subscription->status->isSuspended()) {
            return;
        }

        (new UpdateSubscriptionStatus())($subscription, SubscriptionStatus::SUSPENDED(), $message);
    }
}
