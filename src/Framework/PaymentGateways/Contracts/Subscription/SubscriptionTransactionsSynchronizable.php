<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
interface SubscriptionTransactionsSynchronizable
{
    /**
     * Synchronizes a subscription and its transactions with the gateway.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     *
     * @return void
     */
    public function synchronizeSubscription(Subscription $subscriptionModel);
}
