<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

class SubscriptionCompleteHandler
{
    /**
     * @unreleased replace logic with models
     * @since 2.18.0
     *
     * @param  SubscriptionComplete  $subscriptionComplete
     * @param  Subscription  $subscription
     * @param  Donation  $donation
     * @return void
     * @throws Exception
     */
    public function __invoke(SubscriptionComplete $subscriptionComplete, $subscription, $donation)
    {
        $donation->status = DonationStatus::COMPLETE();
        $donation->gatewayTransactionId = $subscriptionComplete->gatewayTransactionId;
        $donation->save();

        $subscription->status = SubscriptionStatus::ACTIVE();
        $subscription->gatewaySubscriptionId = $subscriptionComplete->gatewaySubscriptionId;
        $subscription->transactionId = $subscriptionComplete->gatewayTransactionId;
        $subscription->save();
    }
}
