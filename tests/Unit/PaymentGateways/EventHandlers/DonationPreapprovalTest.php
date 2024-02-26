<?php

namespace Give\Tests\Unit\PaymentGateways\EventHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\PaymentGateways\EventHandlers\DonationPreapproval;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class DonationPreapprovalTest extends TestCase
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldSetStatusToPreapproval()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'gatewayTransactionId' => 'gateway-transaction-id',
            'status' => DonationStatus::PROCESSING(),
        ]);

        try {
            give(DonationPreapproval::class)($donation->gatewayTransactionId);
        } catch (Exception $e) {
            //ignore exception;
        }

        // re-fetch donation
        $donation = Donation::find($donation->id);

        $this->assertTrue($donation->status->isPreapproval());
    }
}
