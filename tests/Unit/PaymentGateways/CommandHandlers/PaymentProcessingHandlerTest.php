<?php

namespace Give\Tests\Unit\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentProcessingHandler;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class PaymentProcessingHandlerTest extends TestCase {
    use RefreshDatabase;

    /**
     * @since 2.27.0
     * @throws TypeNotSupported|Exception
     */
    public function testShouldHandlePaymentProcessingCommand()
    {
        /* @var Donation $donation */
        $donation = Donation::factory()->create([
            'status' => DonationStatus::PENDING(),
        ]);

        $command = new PaymentProcessing('gateway-transaction-id');

        $handler = new PaymentProcessingHandler($command);
        $handler->handle($donation);

        $this->assertEquals(DonationStatus::PROCESSING(), $donation->status);
        $this->assertEquals('gateway-transaction-id', $donation->gatewayTransactionId);
    }
}
