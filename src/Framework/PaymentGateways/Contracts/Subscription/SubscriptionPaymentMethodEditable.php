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
     * @param Subscription $subscriptionModel
     * @param array $arg Additional information about payment method.
     *
     * @return void
     */
    public function updateSubscriptionPaymentMethod(Subscription $subscriptionModel, array $arg = []);
}
