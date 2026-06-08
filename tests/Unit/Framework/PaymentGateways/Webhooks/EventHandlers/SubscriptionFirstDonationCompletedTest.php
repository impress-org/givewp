<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\SubscriptionFirstDonationCompleted;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.6.0
 */
class SubscriptionFirstDonationCompletedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function testShouldUpdateDonationAndSubscriptionStatuses()
    {
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
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function testShouldUpdateOnlyDonationStatus()
    {
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
     *
     * @throws Exception
     */
    public function testShouldUpdateDonationAndSubscriptionStatusesWhenDonationIdIsProvided()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $subscription->status = SubscriptionStatus::PENDING();
        $subscription->save();

        $donation->status = DonationStatus::PENDING();
        $donation->gatewayTransactionId = null;
        $donation->save();

        give(SubscriptionFirstDonationCompleted::class)(
            'gateway-transaction-id',
            '',
            true,
            true,
            'gateway-subscription-id',
            $donation->id
        );

        $subscription = Subscription::find($subscription->id);
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isComplete());
        $this->assertSame('gateway-transaction-id', $donation->gatewayTransactionId);
        $this->assertTrue($subscription->status->isActive());
        $this->assertSame('gateway-subscription-id', $subscription->gatewaySubscriptionId);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldBindTransactionIdToExistingDonationWhenDonationIdIsProvided()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $donation->status = DonationStatus::PENDING();
        $donation->gatewayTransactionId = null;
        $donation->save();

        give(SubscriptionFirstDonationCompleted::class)(
            'gateway-transaction-id',
            '',
            false,
            true,
            '',
            $donation->id
        );

        $donation = Donation::find($donation->id);

        $this->assertSame('gateway-transaction-id', $donation->gatewayTransactionId);
        $this->assertTrue($donation->status->isComplete());
    }
}
