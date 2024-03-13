<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class SubscriptionCancelled
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(string $gatewaySubscriptionId, string $message = '')
    {
        $subscription = give()->subscriptions->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ( ! $subscription || $subscription->status->isCancelled()) {
            return;
        }

        (new UpdateSubscriptionStatus())($subscription, SubscriptionStatus::CANCELLED(), $message);
    }
}
