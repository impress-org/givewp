<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\EventHandlers\DonationAbandoned;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class DonationAbandonedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldSetStatusToAbandoned()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::PENDING(),
        ]);

        give(DonationAbandoned::class)($donation->gatewayTransactionId);

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isAbandoned());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldNotSetStatusToAbandonedWhenRecurring()
    {
        $donation = Subscription::factory()->createWithDonation()->initialDonation();
        $donation->gatewayTransactionId = 'gateway-transaction-id';
        $donation->save();

        give(DonationAbandoned::class)($donation->gatewayTransactionId, 'test', true);

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertNotTrue($donation->status->isAbandoned());
    }
}
