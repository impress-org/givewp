<?php

namespace Give\Tests\Unit\PaymentGateways\Gateways\TestGateway;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 *
 * @covers \Give\PaymentGateways\Gateways\TestGateway\TestGateway
 */
class TestGatewayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testCreatePaymentCompletesWhileTestModeIsEnabled(): void
    {
        add_filter('give_is_test_mode', '__return_true');

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['gatewayId' => TestGateway::id()]);
        $intent = 'test-gateway-intent';

        $command = give(TestGateway::class)->createPayment($donation, ['testGatewayIntent' => $intent]);

        $this->assertInstanceOf(PaymentComplete::class, $command);
        $this->assertSame("test-gateway-transaction-id-{$intent}-{$donation->id}", $command->gatewayTransactionId);
    }

    /**
     * @since TBD
     */
    public function testCreatePaymentIsRefusedWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['gatewayId' => TestGateway::id()]);

        $this->expectException(PaymentGatewayException::class);

        give(TestGateway::class)->createPayment($donation, []);
    }

    /**
     * @since TBD
     */
    public function testCreateSubscriptionIsRefusedWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation(['gatewayId' => TestGateway::id()]);

        $this->expectException(PaymentGatewayException::class);

        give(TestGateway::class)->createSubscription($subscription->initialDonation(), $subscription, []);
    }
}
