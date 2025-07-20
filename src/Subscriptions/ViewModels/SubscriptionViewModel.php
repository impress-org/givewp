<?php

namespace Give\Subscriptions\ViewModels;

use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
class SubscriptionViewModel
{
    private Subscription $subscription;

    /**
     * @unreleased
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * @unreleased
     */
    public function exports(): array
    {
        $data = array_merge(
            $this->subscription->toArray(),
            [
                'gateway' => $this->getGatewayDetails(),
            ]
        );

        return $data;
    }

    /**
     * @unreleased
     */
    private function getGatewayDetails(): array
    {
        return array_merge(
            $this->subscription->gateway()->toArray(),
            [
                'transactionUrl' => '', // TODO: Add gateway-specific subscription management URL if available
            ]
        );
    }
}
