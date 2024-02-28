<?php

namespace Give\Tests\Unit\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\EventHandlers\DonationFailed;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class DonationFailedTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldSetStatusToFailed()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::PENDING(),
        ]);

        try {
            give(DonationFailed::class)($donation->gatewayTransactionId);
        } catch (Exception $e) {
            //ignore exception;
        }

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isFailed());
    }
}