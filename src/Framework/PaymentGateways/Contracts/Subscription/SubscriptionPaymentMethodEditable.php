<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;
use Give\ValueObjects\CardInfo;

/**
 * @unreleased
 */
interface SubscriptionPaymentMethodEditable
{
    /**
     * Update subscription payment method.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     * @param array $arg Additional information about payment method.
     *
     * @return void
     */
    public function updateSubscriptionPaymentMethod(Subscription $subscriptionModel, array $arg = []);
}
