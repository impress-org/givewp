<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Commands;

use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionCompleteHandler;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Helpers\Call;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 2.23.2
 */
class SubscriptionCompleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 2.23.2
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
