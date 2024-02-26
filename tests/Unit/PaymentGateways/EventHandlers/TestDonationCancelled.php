<?php

namespace Unit\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\EventHandlers\DonationCancelled;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class TestDonationCancelled extends TestCase
{
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

        try {
            give(DonationCancelled::class)($donation->gatewayTransactionId);
        } catch (Exception $e) {
            //ignore exception;
        }

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isCancelled());
    }
}
