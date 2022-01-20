<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give_Subscription;
use Give_Subscriptions_DB;

class SubscriptionCompleteHandler  {
    /**
     * @since 2.18.0
     *
     * @param  SubscriptionComplete  $subscriptionComplete
     * @param  int  $subscriptionId
     * @param  int  $donationId
     * @return void
     */
    public function __invoke(SubscriptionComplete $subscriptionComplete, $subscriptionId, $donationId)
    {
        give_update_payment_status($donationId);
        give_set_payment_transaction_id($donationId, $subscriptionComplete->gatewayTransactionId);

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
     * @since 2.18.0
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
     * @since 2.18.0
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
