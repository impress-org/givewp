<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @since 3.17.0
 */
interface SubscriptionPausable
{
    /**
     * Pause subscription.
     *
     * @since 3.17.0
     */
    public function pauseSubscription(Subscription $subscription, array $data): void;

    /**
     * Resume subscription.
     *
     * @since 3.17.0
     */
    public function resumeSubscription(Subscription $subscription): void;

    /**
     * Check if subscription can be paused.
     *
     * @since 3.17.0
     */
    public function canPauseSubscription(): bool;
}
