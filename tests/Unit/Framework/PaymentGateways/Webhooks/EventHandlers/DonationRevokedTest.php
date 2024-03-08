<?php

namespace Give\Tests\Unit\Framework\PaymentGateways\Webhooks\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Webhooks\EventHandlers\DonationRevoked;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class DonationRevokedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldSetStatusToRevoked()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::COMPLETE(),
        ]);

        give(DonationRevoked::class)($donation->gatewayTransactionId);

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isRevoked());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldNotSetStatusToRevokedWhenRecurring()
    {
        $donation = Subscription::factory()->createWithDonation()->initialDonation();
        $donation->gatewayTransactionId = 'gateway-transaction-id';
        $donation->save();

        give(DonationRevoked::class)($donation->gatewayTransactionId, 'test', true);

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertNotTrue($donation->status->isRevoked());
    }
}
