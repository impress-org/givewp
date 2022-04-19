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
     * @unreleased
     *
     * @param Subscription $subscription
     *
     * @return void
     */
    public function cancelSubscription(Subscription $subscription);

    /**
     * Refund subscription.
     *
     * @unreleased
     *
     * @param Subscription $subscription
     * @param Donation $donation
     */
    public function refundSubscription(Subscription $subscription, Donation $donation);

    /**
     * Returns whether the gateway supports syncing subscriptions.
     *
     * @unreleased
     *
     * @return bool
     */
    public function canSyncSubscriptionWithPaymentGateway();

    /**
     * Whether the gateway supports updating subscription amount.
     *
     * @unreleased
     *
     * @return bool
     */
    public function canUpdateSubscriptionAmount();

    /**
     * Whether the gateway supports updating subscription method.
     *
     * @unreleased
     *
     * @return bool
     */
    public function canUpdateSubscriptionPaymentMethod();
}
