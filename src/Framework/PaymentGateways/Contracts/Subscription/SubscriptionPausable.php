<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use DateTime;
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
    public function pauseSubscription(Subscription $subscription, DateTime $resumesAt);

    /**
     * Resume subscription.
     *
     * @unreleased
     */
    public function resumeSubscription(Subscription $subscription);

    /**
     * Check if subscription can be paused.
     *
     * @unreleased
     */
    public function canPauseSubscription(): bool;
}
