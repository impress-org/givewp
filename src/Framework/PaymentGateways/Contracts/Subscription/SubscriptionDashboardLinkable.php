<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
interface SubscriptionDashboardLinkable
{
    /**
     * @unreleased
     */
    public function gatewayDashboardSubscriptionUrl(Subscription $subscription): string;
}
