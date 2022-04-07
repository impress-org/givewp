<?php

namespace Give\Framework\PaymentGateways\Contracts;

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
     * Return flag whether subscription cancelable.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     *
     * @return bool
     */
    public function canCancelSubscription(Subscription $subscriptionModel);

    /**
     * Return flag whether subscription synchronizable.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     *
     * @return bool
     */
    public function canSyncSubscriptionWithPaymentGateway(Subscription $subscriptionModel);

    /**
     * Return flag whether subscription editable.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     *
     * @return bool
     */
    public function canUpdateSubscription(Subscription $subscriptionModel);

    /**
     * Cancel subscription.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     *
     * @return void
     */
    public function cancelSubscription(Subscription $subscriptionModel);
}
