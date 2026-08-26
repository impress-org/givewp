<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalStandard;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\PayPalStandard\Controllers\PayPalStandardWebhook;
use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\WebhookValidator;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Cache_Setting;
use ReflectionClass;

/**
 * @since 4.16.6.1 Add tests for PayPal Standard IPN event-data validation.
 */
class PayPalStandardWebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var PayPalStandardWebhook
     */
    private $webhook;

    /**
     * @var Donation
     */
    private $donation;

    /**
     * @var int
     */
    private $formId;

    public function setUp(): void
    {
        parent::setUp();

        give_update_option('paypal_email', 'merchant@testsite.com');
        Give_Cache_Setting::get_instance()->reload_plugin_settings('give_settings');

        $donationForm = DonationForm::factory()->create();
        $this->formId = $donationForm->id;

        $this->donation = Donation::factory()->create([
            'formId'               => $this->formId,
            'gatewayId'            => 'paypal',
            'status'               => DonationStatus::PENDING(),
            'amount'               => new Money(1000, 'USD'),
        ]);

        $webhookValidator = new WebhookValidator();
        $this->webhook    = new PayPalStandardWebhook($webhookValidator);
    }

    public function tearDown(): void
    {
        wp_delete_post($this->formId, true);

        parent::tearDown();
    }

    /**
     * Helper: invoke a private method on $this->webhook via reflection.
     */
    private function invokePrivateMethod(string $methodName, ...$args)
    {
        $reflection = new ReflectionClass(PayPalStandardWebhook::class);
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invoke($this->webhook, ...$args);
    }

    /*
     * ── verifyReceiverEmail ──────────────────────────────────────────────────
     */

    /**
     * @since 4.16.6.1
     */
    public function testReceiverEmailMatchesSiteEmail(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'receiver_email' => 'merchant@testsite.com',
        ]);

        $this->assertTrue($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testBusinessEmailMatchesSiteEmail(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'business' => 'merchant@testsite.com',
        ]);

        $this->assertTrue($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testCaseInsensitiveReceiverEmailMatch(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'receiver_email' => 'Merchant@TestSite.com',
        ]);

        $this->assertTrue($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testMismatchedReceiverEmailIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'receiver_email' => 'attacker@evil.com',
        ]);

        $this->assertFalse($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testMismatchedBusinessEmailIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'business' => 'attacker@evil.com',
        ]);

        $this->assertFalse($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testEmptySitePaypalEmailPasses(): void
    {
        give_update_option('paypal_email', '');

        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'receiver_email' => 'anyone@example.com',
        ]);

        $this->assertTrue($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testMissingBothReceiverAndBusinessEmailPasses(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', []);

        $this->assertTrue($result);
    }

    /*
     * ── verifyPaymentAmount ──────────────────────────────────────────────────
     */

    /**
     * @since 4.16.6.1
     */
    public function testMatchingAmountAndCurrencyPasses(): void
    {
        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '10.00',
            'mc_currency' => 'USD',
        ], $this->donation->id);

        $this->assertTrue($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testMismatchedAmountIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '0.01',
            'mc_currency' => 'USD',
        ], $this->donation->id);

        $this->assertFalse($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testMismatchedCurrencyIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '10.00',
            'mc_currency' => 'EUR',
        ], $this->donation->id);

        $this->assertFalse($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testZeroAmountDoesNotMatch(): void
    {
        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '0',
            'mc_currency' => 'USD',
        ], $this->donation->id);

        $this->assertFalse($result);
    }

    /*
     * ── verifyEventData integration ──────────────────────────────────────────
     */

    /**
     * @since 4.16.6.1
     */
    public function testLegitimateCompletedIpnPassesAllChecks(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Completed',
            'receiver_email' => 'merchant@testsite.com',
            'mc_gross'       => '10.00',
            'mc_currency'    => 'USD',
        ], $this->donation->id, 'web_accept');

        $this->assertTrue($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testCompletedIpnWithWrongReceiverEmailFails(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Completed',
            'receiver_email' => 'attacker@evil.com',
            'mc_gross'       => '10.00',
            'mc_currency'    => 'USD',
        ], $this->donation->id, 'web_accept');

        $this->assertFalse($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testCompletedIpnWithWrongAmountFails(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Completed',
            'receiver_email' => 'merchant@testsite.com',
            'mc_gross'       => '0.01',
            'mc_currency'    => 'USD',
        ], $this->donation->id, 'web_accept');

        $this->assertFalse($result);
    }

    /**
     * @since 4.16.6.1
     */
    public function testPendingIpnWithCorrectAmountPasses(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Pending',
            'receiver_email' => 'merchant@testsite.com',
            'mc_gross'       => '10.00',
            'mc_currency'    => 'USD',
        ], $this->donation->id, 'web_accept');

        $this->assertTrue($result);
    }
}
