<?php

namespace Give\Tests\Unit\PaymentGateways\CommandHandlers;

use Exception;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\FieldsAPI\Exceptions\TypeNotSupported;
use Give\Framework\PaymentGateways\CommandHandlers\SubscriptionCompleteHandler;
use Give\Framework\PaymentGateways\Commands\SubscriptionComplete;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class SubscriptionCompleteHandlerTest extends TestCase {
    use RefreshDatabase;

    /**
     * @unreleased
     * @throws TypeNotSupported|Exception
     */
    public function testShouldHandlePaymentCompleteCommand()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();

        $command = new SubscriptionComplete('gateway-transaction-id', 'gateway-subscription-id');

        (new SubscriptionCompleteHandler())($command, $subscription, $donation);

        $this->assertEquals(DonationStatus::COMPLETE(), $donation->status);
        $this->assertEquals(SubscriptionStatus::ACTIVE(), $subscription->status);
        $this->assertEquals('gateway-transaction-id', $donation->gatewayTransactionId);
        $this->assertEquals('gateway-transaction-id', $subscription->transactionId);
        $this->assertEquals('gateway-subscription-id', $subscription->gatewaySubscriptionId);
    }
}