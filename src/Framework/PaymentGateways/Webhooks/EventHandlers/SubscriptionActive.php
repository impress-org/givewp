<?php

namespace Give\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class SubscriptionActive
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function __invoke(
        string $gatewaySubscriptionId,
        string $message = '',
        bool $initialDonationShouldBeCompleted = false
    )
    {
        $subscription = give()->subscriptions->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ( ! $subscription || $subscription->status->isActive()) {
            return;
        }

        if ($initialDonationShouldBeCompleted && ! $subscription->initialDonation()->status->isComplete()) {
            return;
        }

        (new UpdateSubscriptionStatus())($subscription, SubscriptionStatus::ACTIVE(), $message);
    }
}
