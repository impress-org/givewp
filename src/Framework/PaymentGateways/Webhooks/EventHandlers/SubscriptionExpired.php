<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class SubscriptionExpired
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(string $gatewaySubscriptionId, string $message = '')
    {
        $subscription = give()->subscriptions->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ( ! $subscription || $subscription->status->isExpired()) {
            return;
        }

        (new UpdateSubscriptionStatus())($subscription, SubscriptionStatus::EXPIRED(), $message);
    }
}
