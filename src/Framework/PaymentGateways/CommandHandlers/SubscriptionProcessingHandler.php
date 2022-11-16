<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\SubscriptionProcessing;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

class SubscriptionProcessingHandler
{
    /**
     * @unreleased
     * @throws Exception
     */
    public function __invoke(
        SubscriptionProcessing $subscriptionComplete,
        Subscription $subscription,
        Donation $donation
    ) {
        $donation->status = DonationStatus::PROCESSING();
        $subscription->status = SubscriptionStatus::PENDING();
        $subscription->gatewaySubscriptionId = $subscriptionComplete->gatewaySubscriptionId;

        // Only save no-empty gateway transaction ids.
        if ($subscriptionComplete->gatewayTransactionId) {
            $donation->gatewayTransactionId = $subscriptionComplete->gatewayTransactionId;
            $subscription->transactionId = $subscriptionComplete->gatewayTransactionId;
        }

        $donation->save();
        $subscription->save();
    }
}
