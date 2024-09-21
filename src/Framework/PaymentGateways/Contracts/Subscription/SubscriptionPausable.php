<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
interface SubscriptionPausable
{
    /**
     * Pause subscription.
     *
     * @unreleased
     */
    public function pauseSubscription(Subscription $subscription, int $intervalInMonths): void;

    /**
     * Resume subscription.
     *
     * @unreleased
     */
    public function resumeSubscription(Subscription $subscription): void;

    /**
     * Check if subscription can be paused.
     *
     * @unreleased
     */
    public function canPauseSubscription(): bool;
}
