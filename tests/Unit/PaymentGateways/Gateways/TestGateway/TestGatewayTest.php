<?php

namespace Give\Tests\Unit\PaymentGateways\Gateways\TestGateway;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
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
        wp_set_current_user(0);

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
    public function testCreatePaymentIsRefusedForVisitorsWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');
        wp_set_current_user(0);

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['gatewayId' => TestGateway::id()]);

        $this->expectException(PaymentGatewayException::class);

        give(TestGateway::class)->createPayment($donation, []);
    }

    /**
     * @since TBD
     */
    public function testCreatePaymentRecordsATestDonationForAdminsWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['gatewayId' => TestGateway::id(), 'mode' => DonationMode::LIVE()]);

        $command = give(TestGateway::class)->createPayment($donation, []);

        $this->assertInstanceOf(PaymentComplete::class, $command);
        $this->assertTrue(Donation::find($donation->id)->mode->isTest());
    }

    /**
     * @since TBD
     */
    public function testCreateSubscriptionIsRefusedForVisitorsWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');
        wp_set_current_user(0);

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation(['gatewayId' => TestGateway::id()]);

        $this->expectException(PaymentGatewayException::class);

        give(TestGateway::class)->createSubscription($subscription->initialDonation(), $subscription, []);
    }

    /**
     * @since TBD
     */
    public function testCreateSubscriptionRecordsTestRecordsForAdminsWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation([
            'gatewayId' => TestGateway::id(),
            'mode' => SubscriptionMode::LIVE(),
        ]);
        $donation = $subscription->initialDonation();

        $subscriptionModule = $this->createMock(SubscriptionModule::class);
        $subscriptionModule->expects($this->once())->method('createSubscription');

        (new TestGateway($subscriptionModule))->createSubscription($donation, $subscription, []);

        $this->assertTrue(Subscription::find($subscription->id)->mode->isTest());
        $this->assertTrue(Donation::find($donation->id)->mode->isTest());
    }
}
