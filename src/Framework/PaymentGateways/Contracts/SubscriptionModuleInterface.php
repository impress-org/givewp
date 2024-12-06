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
     * Note: You can use "givewp_create_subscription_gateway_data_{$gatewayId}" filter hook to pass additional data for gateway which helps/require to process initial subscription transaction.
     *       This filter will help to add additional arguments to this function which should be optional otherwise you will get PHP fatal error.
     *
     * @since 2.21.2 Add third param to function to pass gateway data to process transaction
     * @since 2.18.0
     *
     * @param array $gatewayData
     *
     * @return GatewayCommand|RedirectOffsite
     */
    public function createSubscription(Donation $donation, Subscription $subscription, $gatewayData);

    /**
     * Cancel subscription.
     *
     * @since 2.20.0
     */
    public function cancelSubscription(Subscription $subscription);

    /**
     * Whether the gateway supports pausing subscriptions.
     *
     * @since 3.17.0
     */
    public function canPauseSubscription(): bool;

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
