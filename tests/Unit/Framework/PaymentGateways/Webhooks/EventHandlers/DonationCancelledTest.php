<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationCancelled;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.6.0
 */
class DonationCancelledTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.6.0
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
     * @since 3.6.0
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
