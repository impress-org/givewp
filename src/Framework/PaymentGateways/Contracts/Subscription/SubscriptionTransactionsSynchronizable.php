<?php

namespace Give\Framework\PaymentGateways\Contracts\Subscription;

use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
interface SubscriptionTransactionsSynchronizable
{
    /**
     * Get subscription transactions from gateway.
     *
     * @unreleased
     *
     * @param Subscription $subscriptionModel
     * @param array $args
     *
     * @return mixed
     */
    public function getSubscriptionTransactionsFromPaymentGateway(Subscription $subscriptionModel, array $args = []);

    /**
     * Get subscription transactions from gateway.
     *
     * @unreleased
     *
     * @param mixed $gatewayTransaction
     * @param Subscription $subscriptionModel
     *
     * @return mixed
     */
    public function canSyncSubscriptionTransaction($gatewayTransaction, Subscription $subscriptionModel);
}
