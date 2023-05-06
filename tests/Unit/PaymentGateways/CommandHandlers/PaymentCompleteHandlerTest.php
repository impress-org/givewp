<?php

namespace Give\Tests\Unit\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentCompleteHandler;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class PaymentCompleteHandlerTest extends TestCase {
    use RefreshDatabase;

    /**
     * @since 2.27.0
     * @throws TypeNotSupported|Exception
     */
    public function testShouldHandlePaymentCompleteCommand()
    {
        /* @var Donation $donation */
        $donation = Donation::factory()->create([
            'status' => DonationStatus::PENDING(),
        ]);

        $command = new PaymentComplete('gateway-transaction-id');

        $handler = new PaymentCompleteHandler($command);
        $handler->handle($donation);

        $this->assertEquals(DonationStatus::COMPLETE(), $donation->status);
        $this->assertEquals('gateway-transaction-id', $donation->gatewayTransactionId);
    }
}
