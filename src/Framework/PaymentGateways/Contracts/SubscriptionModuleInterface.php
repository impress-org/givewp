<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Subscriptions\Models\Subscription;

interface SubscriptionModuleInterface
{
    /**
     * Create a subscription with gateway
     *
     * @since 2.18.0
     *
     * @return GatewayCommand|RedirectOffsite
     */
    public function createSubscription(Donation $donation, Subscription $subscription);

    /**
     * Cancel subscription.
     *
     * @since 2.20.0
     */
    public function cancelSubscription(Subscription $subscription);

    /**
     * Returns whether the gateway supports syncing subscriptions.
     *
     * @since 2.20.0
     */
    public function canSyncSubscriptionWithPaymentGateway(): bool;

    /**
     * Whether the gateway supports updating subscription amount.
     *
     * @since 2.20.0
     */
    public function canUpdateSubscriptionAmount(): bool;

    /**
     * Whether the gateway supports updating subscription method.
     *
     * @since 2.20.0
     */
    public function canUpdateSubscriptionPaymentMethod(): bool;
}
