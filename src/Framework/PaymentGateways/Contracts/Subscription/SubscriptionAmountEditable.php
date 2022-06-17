<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;

/**
 * @since 2.20.0
 */
interface SubscriptionAmountEditable
{
    /**
     * Update subscription amount.
     *
     * @unreleased Add second argument to specify the new amount.
     * @since 2.20.0
     */
    public function updateSubscriptionAmount(Subscription $subscription, Money $newRenewalAmount);
}
