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
     * @since 2.21.0 replace logic with models
     * @since 2.18.0
     *
     * @throws Exception
     */
    public function __invoke(SubscriptionComplete $subscriptionComplete, Subscription $subscription, Donation $donation)
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
