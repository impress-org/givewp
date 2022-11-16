<?php

namespace GiveTests\Unit\Framework\PaymentGateways\Commands;

use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionCompleteHandler;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Helpers\Call;
use Give\Subscriptions\Models\Subscription;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class SubscriptionCompleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @return void
     */
    public function testSubscriptionActiveAndDonationCompleted()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $subscriptionCompleteCommand = new SubscriptionComplete('1234', 'abdc');

        Call::invoke(
            SubscriptionCompleteHandler::class,
            $subscriptionCompleteCommand,
            $subscription,
            $donation
        );

        $this->assertEquals('active', $subscription->status->getValue());
        $this->assertEquals('publish', $donation->status->getValue());
    }
}
