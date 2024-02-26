<?php

namespace Unit\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\EventHandlers\DonationProcessing;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class TestDonationProcessing extends TestCase
{
    /**
     * @unreleased
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

        try {
            give(DonationProcessing::class)($donation->gatewayTransactionId);
        } catch (Exception $e) {
            //ignore exception;
        }

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isProcessing());
    }
}
