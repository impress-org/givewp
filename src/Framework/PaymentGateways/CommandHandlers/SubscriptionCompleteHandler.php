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
     * @unreleased Assign donation and subscription status.
     * @since 2.21.0 replace logic with models
     * @since 2.18.0
     *
     * @throws Exception
     */
    public function __invoke(SubscriptionComplete $subscriptionComplete, Subscription $subscription, Donation $donation)
    {
        $donation->status = $subscriptionComplete->donationStatus ?: DonationStatus::COMPLETE();
        $donation->gatewayTransactionId = $subscriptionComplete->gatewayTransactionId;
        $donation->save();

        echo 'pass';

        $subscription->status = $subscriptionComplete->subscriptionStatus ?: SubscriptionStatus::ACTIVE();
        $subscription->gatewaySubscriptionId = $subscriptionComplete->gatewaySubscriptionId;
        $subscription->transactionId = $subscriptionComplete->gatewayTransactionId;
        $subscription->save();
    }
}
