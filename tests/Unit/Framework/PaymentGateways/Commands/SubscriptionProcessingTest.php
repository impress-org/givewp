<?php

namespace GiveTests\Unit\Framework\PaymentGateways\Commands;

use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionProcessingHandler;
use Give\Framework\PaymentGateways\Commands\SubscriptionProcessing;
use Give\Helpers\Call;
use Give\Subscriptions\Models\Subscription;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

class SubscriptionProcessingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @return void
     */
    public function testSubscriptionPendingAndDonationProcessing()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $subscriptionProcessingCommand = new SubscriptionProcessing('1234', null);

        $handler = new SubscriptionProcessingHandler($subscriptionProcessingCommand, $subscription, $donation);
        $handler();

        $this->assertEquals('pending', $subscription->status->getValue());
        $this->assertEquals('processing', $donation->status->getValue());
    }
}
