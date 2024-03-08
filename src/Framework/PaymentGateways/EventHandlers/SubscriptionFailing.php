<?php

namespace Give\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class SubscriptionFailing
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(string $gatewaySubscriptionId, string $message = '')
    {
        $subscription = give()->subscriptions->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ( ! $subscription || $subscription->status->isFailing()) {
            return;
        }

        (new UpdateSubscriptionStatus())($subscription, SubscriptionStatus::FAILING(), $message);
    }
}
