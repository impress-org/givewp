<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give_Subscription;
use Give_Subscriptions_DB;

class SubscriptionCompleteHandler  {
    /**
     * @unreleased
     *
     * @param  SubscriptionComplete  $subscriptionComplete
     * @param int $subscriptionId
     * @param  int  $paymentId
     * @return void
     */
    public function __invoke(SubscriptionComplete $subscriptionComplete, $subscriptionId, $paymentId)
    {
        give_update_payment_status($paymentId);
        give_set_payment_transaction_id($paymentId, $subscriptionComplete->gatewayTransactionId);

        if (function_exists('give_recurring_update_subscription_status') && class_exists('Give_Subscriptions_DB')) {
            give_recurring_update_subscription_status($subscriptionId, 'active');

            $subscription = $this->getSubscription($subscriptionId);

            $subscription->update([
                'profile_id' => $subscriptionComplete->gatewaySubscriptionId,
                'transaction_id' => $subscriptionComplete->gatewayTransactionId
            ]);
        }
    }

     /**
     * @unreleased
     *
     * @return Give_Subscriptions_DB
     */
    private function subscriptions()
    {
        /**
         * @var Give_Subscriptions_DB $subscriptions
         */
        return give(Give_Subscriptions_DB::class);
    }

    /**
     * @unreleased
     *
     * @param  int  $subscriptionId
     *
     * @return Give_Subscription
     */
    private function getSubscription($subscriptionId)
    {
        return current($this->subscriptions()->get_subscriptions(['id' => $subscriptionId]));
    }
}