<?php

namespace Give\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Framework\PaymentGateways\Actions\UpdateSubscriptionStatus;
use Give\Subscriptions\Repositories\SubscriptionRepository;
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
        $subscription = give(SubscriptionRepository::class)->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ( ! $subscription || $subscription->status->isActive()) {
            return;
        }

        if ($initialDonationShouldBeCompleted && ! $subscription->initialDonation()->status->isComplete()) {
            return;
        }

        (new UpdateSubscriptionStatus())($subscription, SubscriptionStatus::ACTIVE(), $message);
    }
}
