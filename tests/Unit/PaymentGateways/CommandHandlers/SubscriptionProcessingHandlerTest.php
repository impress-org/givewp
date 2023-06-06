<?php

namespace Give\Tests\Unit\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionProcessingHandler;
use Give\Framework\PaymentGateways\Commands\SubscriptionProcessing;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class SubscriptionProcessingHandlerTest extends TestCase {
    use RefreshDatabase;

    /**
     * @since 2.27.0
     * @throws TypeNotSupported|Exception
     */
    public function testShouldHandlePaymentCompleteCommand()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $command = new SubscriptionProcessing('gateway-subscription-id', 'gateway-transaction-id');

        $handler = new SubscriptionProcessingHandler($command, $subscription, $donation);
        $handler();

        $this->assertEquals(DonationStatus::PROCESSING(), $donation->status);
        $this->assertEquals(SubscriptionStatus::PENDING(), $subscription->status);
        $this->assertEquals('gateway-transaction-id', $donation->gatewayTransactionId);
        $this->assertEquals('gateway-transaction-id', $subscription->transactionId);
        $this->assertEquals('gateway-subscription-id', $subscription->gatewaySubscriptionId);
    }
}
