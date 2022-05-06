<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Subscriptions\Models\Subscription;

interface SubscriptionModuleInterface
{
    /**
     * Create a subscription with gateway
     *
     * @since 2.18.0
     */
    public function createSubscription(Donation $donation, Subscription $subscription): GatewayCommand;

    /**
     * Cancel subscription.
     *
     * @unreleased
     */
    public function cancelSubscription(Subscription $subscription);

    /**
     * Returns whether the gateway supports syncing subscriptions.
     *
     * @unreleased
     */
    public function canSyncSubscriptionWithPaymentGateway(): bool;

    /**
     * Whether the gateway supports updating subscription amount.
     *
     * @unreleased
     */
    public function canUpdateSubscriptionAmount(): bool;

    /**
     * Whether the gateway supports updating subscription method.
     *
     * @unreleased
     */
    public function canUpdateSubscriptionPaymentMethod(): bool;
}
