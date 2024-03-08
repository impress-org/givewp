<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Actions\UpdateDonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionFirstDonationCompleted;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class SubscriptionFirstDonationCompletedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldUpdateDonationAndSubscriptionStatuses()
    {
        $this->maybeSkip();

        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $subscription->status = SubscriptionStatus::PENDING();
        $subscription->save();

        $donation->status = DonationStatus::PENDING();
        $donation->gatewayTransactionId = 'gateway-transaction-id';
        $donation->save();

        give(SubscriptionFirstDonationCompleted::class)($donation->gatewayTransactionId);

        $subscription = Subscription::find($subscription->id); // Re-fetch
        $donation = Donation::find($donation->id); // Re-fetch

        $this->assertTrue($subscription->status->isActive());
        $this->assertTrue($donation->status->isComplete());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldUpdateOnlyDonationStatus()
    {
        $this->maybeSkip();

        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $subscription->status = SubscriptionStatus::PENDING();
        $subscription->save();

        $donation->status = DonationStatus::PENDING();
        $donation->gatewayTransactionId = 'gateway-transaction-id';
        $donation->save();

        give(SubscriptionFirstDonationCompleted::class)($donation->gatewayTransactionId, 'test', false);

        $subscription = Subscription::find($subscription->id); // Re-fetch
        $donation = Donation::find($donation->id); // Re-fetch

        $this->assertTrue($subscription->status->isPending());
        $this->assertTrue($donation->status->isComplete());
    }

    /**
     * @unreleased
     */
    private function maybeSkip()
    {
        if ( ! class_exists(UpdateDonationStatus::class)) {
            $this->markTestSkipped('UpdateSubscriptionStatus class does not exist in GiveWP');
        }
    }
}
