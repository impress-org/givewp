<?php

namespace GiveTests\Unit\Framework\PaymentGateways\Commands;

use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionCompleteHandler;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Helpers\Call;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
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
        $donation = $subscription->donations()->limit(1)->orderBy('ID', 'ASC')->get();

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

    /**
     * @unreleased
     * @return void
     */
    public function testSubscriptionPendingAndDonationProcessing()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->donations()->limit(1)->orderBy('ID', 'ASC')->get();

        $subscriptionCompleteCommand = new SubscriptionComplete(
            '1234',
            'abdc',
            SubscriptionStatus::PENDING(),
            DonationStatus::PROCESSING()
        );

        Call::invoke(
            SubscriptionCompleteHandler::class,
            $subscriptionCompleteCommand,
            $subscription,
            $donation
        );

        $this->assertEquals('pending', $subscription->status->getValue());
        $this->assertEquals('processing', $donation->status->getValue());
    }
}
