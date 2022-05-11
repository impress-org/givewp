<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.20.0
 */
interface SubscriptionTransactionsSynchronizable
{
    /**
     * Synchronizes a subscription and its transactions with the gateway.
     *
     * @since 2.20.0
     *
     * @param Subscription $subscriptionModel
     *
     * @return void
     */
    public function synchronizeSubscription(Subscription $subscriptionModel);
}
