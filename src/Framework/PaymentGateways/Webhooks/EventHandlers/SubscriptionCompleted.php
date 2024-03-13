<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @since 3.6.0
 */
class SubscriptionCompleted
{
    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function __invoke(string $gatewaySubscriptionId, string $message = '')
    {
        $subscription = give()->subscriptions->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ( ! $subscription || $subscription->status->isCompleted()) {
            return;
        }

        (new UpdateSubscriptionStatus())($subscription, SubscriptionStatus::COMPLETED(), $message);
    }
}
