<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.21.2
 */
interface SubscriptionDashboardLinkable
{
    /**
     * @since 2.21.2
     */
    public function gatewayDashboardSubscriptionUrl(Subscription $subscription): string;
}
