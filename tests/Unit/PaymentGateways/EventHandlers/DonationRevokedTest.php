<?php

namespace Give\Tests\Unit\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\EventHandlers\DonationRevoked;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class DonationRevokedTest extends TestCase
{
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

        try {
            give(DonationRevoked::class)($donation->gatewayTransactionId);
        } catch (Exception $e) {
            //ignore exception;
        }

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isRevoked());
    }
}
