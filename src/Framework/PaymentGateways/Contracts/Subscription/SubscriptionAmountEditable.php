<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
interface SubscriptionAmountEditable
{
    /**
     * Update subscription amount.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     *
     * @return void
     */
    public function updateSubscriptionAmount(Subscription $subscriptionModel);
}
