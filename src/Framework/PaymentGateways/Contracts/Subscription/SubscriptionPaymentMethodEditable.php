<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.20.0
 */
interface SubscriptionPaymentMethodEditable
{
    /**
     * Update subscription payment method.
     *
     * @since 2.20.0
     *
     * @param array $arg Additional information about payment method.
     */
    public function updateSubscriptionPaymentMethod(Subscription $subscription, array $arg = []);
}
