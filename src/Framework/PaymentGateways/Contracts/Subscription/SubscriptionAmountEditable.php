<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.20.0
 */
interface SubscriptionAmountEditable
{
    /**
     * Update subscription amount.
     *
     * @since 2.20.0
     */
    public function updateSubscriptionAmount(Subscription $subscription);
}
