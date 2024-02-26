<?php

namespace Unit\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\EventHandlers\DonationAbandoned;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class TestDonationAbandoned extends TestCase
{
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

        try {
            give(DonationAbandoned::class)($donation->gatewayTransactionId);
        } catch (Exception $e) {
            //ignore exception;
        }

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isAbandoned());
    }
}
