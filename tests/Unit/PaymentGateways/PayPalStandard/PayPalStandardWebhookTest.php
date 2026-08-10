<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalStandard;

use Give\PaymentGateways\Gateways\PayPalStandard\Controllers\PayPalStandardWebhook;
use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\WebhookValidator;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use ReflectionClass;

/**
 * @since TBD Add tests for PayPal Standard IPN event-data validation.
 */
class PayPalStandardWebhookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var PayPalStandardWebhook
     */
    private $webhook;

    /**
     * @var int
     */
    private $donationId;

    /**
     * @var int
     */
    private $formId;

    public function setUp(): void
    {
        parent::setUp();

        give_update_option('paypal_email', 'merchant@testsite.com');

        $this->formId = wp_insert_post([
            'post_title'  => 'Test Donation Form',
            'post_type'   => 'give_forms',
            'post_status' => 'publish',
        ]);

        $this->donationId = give_insert_payment([
            'price'           => '10.00',
            'give_form_title' => 'Test Donation Form',
            'give_form_id'    => $this->formId,
            'give_price_id'   => 0,
            'date'            => current_time('mysql'),
            'user_email'      => 'donor@testsite.com',
            'purchase_key'    => 'test-' . wp_generate_password(12, false),
            'currency'        => 'USD',
            'user_info'       => [
                'id'         => 0,
                'email'      => 'donor@testsite.com',
                'first_name' => 'Test',
                'last_name'  => 'Donor',
                'address'    => [],
            ],
            'status'          => 'pending',
            'gateway'         => 'paypal',
        ]);

        give_update_meta($this->donationId, '_give_payment_transaction_id', 'LEGITIMATE-PARENT-TXN');

        $webhookValidator = new WebhookValidator();
        $this->webhook    = new PayPalStandardWebhook($webhookValidator);
    }

    public function tearDown(): void
    {
        wp_delete_post($this->formId, true);
        wp_delete_post($this->donationId, true);

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
     * @since TBD
     */
    public function testReceiverEmailMatchesSiteEmail(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'receiver_email' => 'merchant@testsite.com',
        ]);

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testBusinessEmailMatchesSiteEmail(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'business' => 'merchant@testsite.com',
        ]);

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testCaseInsensitiveReceiverEmailMatch(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'receiver_email' => 'Merchant@TestSite.com',
        ]);

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testMismatchedReceiverEmailIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'receiver_email' => 'attacker@evil.com',
        ]);

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testMismatchedBusinessEmailIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'business' => 'attacker@evil.com',
        ]);

        $this->assertFalse($result);
    }

    /**
     * @since TBD
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
     * @since TBD
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
     * @since TBD
     */
    public function testMatchingAmountAndCurrencyPasses(): void
    {
        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '10.00',
            'mc_currency' => 'USD',
        ], $this->donationId);

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testMismatchedAmountIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '0.01',
            'mc_currency' => 'USD',
        ], $this->donationId);

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testMismatchedCurrencyIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '10.00',
            'mc_currency' => 'EUR',
        ], $this->donationId);

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testZeroAmountDoesNotMatch(): void
    {
        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '0',
            'mc_currency' => 'USD',
        ], $this->donationId);

        $this->assertFalse($result);
    }

    /*
     * ── verifyParentTransactionId ────────────────────────────────────────────
     */

    /**
     * @since TBD
     */
    public function testMatchingParentTransactionIdPasses(): void
    {
        $result = $this->invokePrivateMethod('verifyParentTransactionId', [
            'parent_txn_id' => 'LEGITIMATE-PARENT-TXN',
        ], $this->donationId);

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testMismatchedParentTransactionIdIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyParentTransactionId', [
            'parent_txn_id' => 'ATTACKER-TXN',
        ], $this->donationId);

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testMissingParentTransactionIdInEventDataPasses(): void
    {
        $result = $this->invokePrivateMethod('verifyParentTransactionId', [], $this->donationId);

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testParentTxnIdPresentButNoStoredTransactionIdIsRejected(): void
    {
        give_update_meta($this->donationId, '_give_payment_transaction_id', '');

        $result = $this->invokePrivateMethod('verifyParentTransactionId', [
            'parent_txn_id' => 'ANY-TXN',
        ], $this->donationId);

        $this->assertFalse($result);
    }

    /*
     * ── verifyEventData integration ──────────────────────────────────────────
     */

    /**
     * @since TBD
     */
    public function testLegitimateCompletedIpnPassesAllChecks(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Completed',
            'receiver_email' => 'merchant@testsite.com',
            'mc_gross'       => '10.00',
            'mc_currency'    => 'USD',
        ], $this->donationId, 'web_accept');

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testCompletedIpnWithWrongReceiverEmailFails(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Completed',
            'receiver_email' => 'attacker@evil.com',
            'mc_gross'       => '10.00',
            'mc_currency'    => 'USD',
        ], $this->donationId, 'web_accept');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testCompletedIpnWithWrongAmountFails(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Completed',
            'receiver_email' => 'merchant@testsite.com',
            'mc_gross'       => '0.01',
            'mc_currency'    => 'USD',
        ], $this->donationId, 'web_accept');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testLegitimateRefundedIpnPasses(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Refunded',
            'receiver_email' => 'merchant@testsite.com',
            'parent_txn_id'  => 'LEGITIMATE-PARENT-TXN',
        ], $this->donationId, 'web_accept');

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testRefundedIpnWithWrongParentTransactionIdFails(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Refunded',
            'receiver_email' => 'merchant@testsite.com',
            'parent_txn_id'  => 'ATTACKER-TXN',
        ], $this->donationId, 'web_accept');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testPendingIpnWithCorrectAmountPasses(): void
    {
        $result = $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Pending',
            'receiver_email' => 'merchant@testsite.com',
            'mc_gross'       => '10.00',
            'mc_currency'    => 'USD',
        ], $this->donationId, 'web_accept');

        $this->assertTrue($result);
    }
}
