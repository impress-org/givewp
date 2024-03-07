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
    public function __invoke(string $gatewaySubscriptionId, string $message = '')
    {
        $subscription = give(SubscriptionRepository::class)->getByGatewaySubscriptionId($gatewaySubscriptionId);

        if ($subscription &&
            ! empty($subscription->initialDonation()->gatewayTransactionId) &&
            $subscription->initialDonation()->status->isComplete()) {
            (new UpdateSubscriptionStatus())(
                $subscription,
                SubscriptionStatus::ACTIVE(),
                $message
            );
        }
    }
}
