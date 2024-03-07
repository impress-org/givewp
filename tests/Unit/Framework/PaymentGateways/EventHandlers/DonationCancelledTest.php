<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\EventHandlers\DonationCancelled;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class DonationCancelledTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldSetStatusToCancelled()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::COMPLETE(),
        ]);

        give(DonationCancelled::class)($donation->gatewayTransactionId);

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isCancelled());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldNotSetStatusToCancelledWhenRecurring()
    {
        $donation = Subscription::factory()->createWithDonation()->initialDonation();
        $donation->gatewayTransactionId = 'gateway-transaction-id';
        $donation->save();

        give(DonationCancelled::class)($donation->gatewayTransactionId, 'test', true);

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertNotTrue($donation->status->isCancelled());
    }
}
