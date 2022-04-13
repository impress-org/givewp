<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
interface SubscriptionPaymentMethodEditable
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
    public function updateSubscriptionPaymentMethod(Subscription $subscriptionModel);
}
