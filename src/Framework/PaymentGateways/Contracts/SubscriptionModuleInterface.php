<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;
use Give\Subscriptions\Models\Subscription;

interface SubscriptionModuleInterface
{
    /**
     * Create a subscription with gateway
     *
     * @since 2.18.0
     *
     * @param GatewayPaymentData $paymentData
     * @param GatewaySubscriptionData $subscriptionData
     *
     * @return GatewayCommand
     */
    public function createSubscription(GatewayPaymentData $paymentData, GatewaySubscriptionData $subscriptionData);

    /**
     * Cancel subscription.
     *
     * @since 2.20.0
     *
     * @param Subscription $subscription
     *
     * @return void
     */
    public function cancelSubscription(Subscription $subscription);

    /**
     * Returns whether the gateway supports syncing subscriptions.
     *
     * @since 2.20.0
     *
     * @return bool
     */
    public function canSyncSubscriptionWithPaymentGateway();

    /**
     * Whether the gateway supports updating subscription amount.
     *
     * @since 2.20.0
     *
     * @return bool
     */
    public function canUpdateSubscriptionAmount();

    /**
     * Whether the gateway supports updating subscription method.
     *
     * @since 2.20.0
     *
     * @return bool
     */
    public function canUpdateSubscriptionPaymentMethod();
}
