<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\SubscriptionProcessing;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @unreleased
 */
class SubscriptionProcessingHandler
{
    /**
     * @unreleased
     * @var SubscriptionProcessing
     */
    private $subscriptionComplete;
    /**
     * @unreleased
     * @var Subscription
     */
    private $subscription;
    /**
     * @unreleased
     * @var Donation
     */
    private $donation;

    /**
     * @unreleased
     */
    public function __construct(
        SubscriptionProcessing $subscriptionComplete,
        Subscription $subscription,
        Donation $donation
    ) {
        $this->subscriptionComplete = $subscriptionComplete;
        $this->subscription = $subscription;
        $this->donation = $donation;
    }

    /**
     * @unreleased
     * @return void
     * @throws Exception
     */
    public function __invoke()
    {
        $this->donation->status = DonationStatus::PROCESSING();
        $this->subscription->status = SubscriptionStatus::PENDING();
        $this->subscription->gatewaySubscriptionId = $this->subscriptionComplete->gatewaySubscriptionId;

        // Only save no-empty gateway transaction ids.
        if ($this->subscriptionComplete->gatewayTransactionId) {
            $this->donation->gatewayTransactionId = $this->subscriptionComplete->gatewayTransactionId;
            $this->subscription->transactionId = $this->subscriptionComplete->gatewayTransactionId;
        }

        $this->donation->save();
        $this->subscription->save();
    }
}
