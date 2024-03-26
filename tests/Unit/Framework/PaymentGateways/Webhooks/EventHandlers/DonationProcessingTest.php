<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationProcessing;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.6.0
 */
class DonationProcessingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function testShouldSetStatusToProcessing()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::PENDING(),
        ]);

        give(DonationProcessing::class)($donation->gatewayTransactionId);

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isProcessing());
    }

    /**
     * @since 3.6.0
     *
     * @throws Exception
     */
    public function testShouldNotSetStatusToProcessingWhenRecurring()
    {
        $donation = Subscription::factory()->createWithDonation()->initialDonation();
        $donation->gatewayTransactionId = 'gateway-transaction-id';
        $donation->save();

        give(DonationProcessing::class)($donation->gatewayTransactionId, 'test', true);

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertNotTrue($donation->status->isProcessing());
    }
}
