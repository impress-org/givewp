<?php

namespace Unit\PaymentGateways\Actions;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\PaymentGateways\Actions\UpdateDonationStatus;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class TestUpdateDonationStatus extends TestCase
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldUpdateStatus()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::PENDING(),
        ]);

        give(UpdateDonationStatus::class)($donation, DonationStatus::COMPLETE());

        $this->assertTrue($donation->status->isComplete());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testInvalidSubscriptionFirstPaymentShouldCancelSubscription()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create([
            'gatewaySubscriptionId' => 'gateway-subscription-id',
            'status' => SubscriptionStatus::PENDING(),
        ]);

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::PENDING(),
            'type' => DonationType::SUBSCRIPTION(),
            'subscriptionId' => $subscription->id,
        ]);

        // Case 1 - Cancelled first payment
        give(UpdateDonationStatus::class)($donation, DonationStatus::CANCELLED());
        $this->assertTrue($subscription->status->isCancelled());

        $donation->status = DonationStatus::PENDING();
        $donation->save();
        $donation = Donation::find($donation->id); // Re-fetch

        $subscription->status = SubscriptionStatus::PENDING();
        $subscription->save();
        $subscription = Subscription::find($subscription->id); // Re-fetch

        // Case 2 - Abandoned first payment
        give(UpdateDonationStatus::class)($donation, DonationStatus::ABANDONED());
        $this->assertTrue($subscription->status->isCancelled());
    }
}
