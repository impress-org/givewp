<?php

namespace Give\Framework\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\SubscriptionProcessing;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;

/**
 * @since 2.23.2
 */
class SubscriptionProcessingHandler
{
    /**
     * @since 2.23.2
     * @var SubscriptionProcessing
     */
    private $subscriptionComplete;
    /**
     * @since 2.23.2
     * @var Subscription
     */
    private $subscription;
    /**
     * @since 2.23.2
     * @var Donation
     */
    private $donation;

    /**
     * @since 2.23.2
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
     * @since 2.23.2
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
