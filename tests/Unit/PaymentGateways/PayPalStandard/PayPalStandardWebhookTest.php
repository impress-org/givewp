<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalStandard;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\PayPalStandard\Controllers\PayPalStandardWebhook;
use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\WebhookValidator;
use Give\Subscriptions\Models\Subscription;
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
     * @since TBD Reject instead of pass when the site has no PayPal email.
     * @since 4.16.6.1
     */
    public function testEmptySitePaypalEmailIsRejected(): void
    {
        give_update_option('paypal_email', '');

        $result = $this->invokePrivateMethod('verifyReceiverEmail', [
            'receiver_email' => 'anyone@example.com',
        ]);

        $this->assertFalse($result);
    }

    /**
     * @since TBD Reject instead of pass when both emails are missing.
     * @since 4.16.6.1
     */
    public function testMissingBothReceiverAndBusinessEmailIsRejected(): void
    {
        $result = $this->invokePrivateMethod('verifyReceiverEmail', []);

        $this->assertFalse($result);
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

    /**
     * PayPal charges the gross amount, which includes the fee recovered by the Fee Recovery add-on.
     *
     * @since TBD
     */
    public function testFeeRecoveredDonationMatchesGrossAmount(): void
    {
        $donation = $this->createFeeRecoveredDonation();

        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '25.84',
            'mc_currency' => 'EUR',
        ], $donation->id, 'web_accept');

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testFeeRecoveredDonationRejectsAmountWithoutFee(): void
    {
        $donation = $this->createFeeRecoveredDonation();

        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '25.00',
            'mc_currency' => 'EUR',
        ], $donation->id, 'web_accept');

        $this->assertFalse($result);
    }

    /**
     * Renewal IPNs reference the initial donation through "custom".
     *
     * @since TBD
     */
    public function testFeeRecoveredRenewalMatchesSubscriptionAmount(): void
    {
        $subscription = $this->createFeeRecoveredSubscription();

        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '25.84',
            'mc_currency' => 'EUR',
        ], $subscription->initialDonation()->id, 'subscr_payment');

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testRenewalMatchesSubscriptionAmountThatDiffersFromInitialDonation(): void
    {
        $subscription = $this->createFeeRecoveredSubscription();
        $subscription->amount = new Money(3000, 'EUR');
        $subscription->save();

        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '30.00',
            'mc_currency' => 'EUR',
        ], $subscription->initialDonation()->id, 'subscr_payment');

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testSubscriptionAmountIsIgnoredForOneTimePayments(): void
    {
        $subscription = $this->createFeeRecoveredSubscription();
        $subscription->amount = new Money(3000, 'EUR');
        $subscription->save();

        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '30.00',
            'mc_currency' => 'EUR',
        ], $subscription->initialDonation()->id, 'web_accept');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testRenewalWithTamperedAmountIsRejected(): void
    {
        $subscription = $this->createFeeRecoveredSubscription();

        $result = $this->invokePrivateMethod('verifyPaymentAmount', [
            'mc_gross'    => '0.01',
            'mc_currency' => 'EUR',
        ], $subscription->initialDonation()->id, 'subscr_payment');

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

    /**
     * @since TBD
     */
    public function testFeeRecoveredRenewalIpnPassesAllChecks(): void
    {
        $subscription = $this->createFeeRecoveredSubscription();

        $result = $this->invokePrivateMethod('verifyEventData', [
            'txn_type'       => 'subscr_payment',
            'payment_status' => 'Completed',
            'receiver_email' => 'merchant@testsite.com',
            'mc_gross'       => '25.84',
            'mc_currency'    => 'EUR',
        ], $subscription->initialDonation()->id, 'subscr_payment');

        $this->assertTrue($result);
    }

    /*
     * ── refunds and reversals ────────────────────────────────────────────────
     */

    /**
     * @since TBD
     */
    public function testFullRefundOfInitialPaymentPasses(): void
    {
        $result = $this->verifyRefund($this->createCompletedDonation()->id, 'INITIAL-TXN', '-10.00', 'USD');

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testPartialRefundPasses(): void
    {
        $result = $this->verifyRefund($this->createCompletedDonation()->id, 'INITIAL-TXN', '-4.00', 'USD');

        $this->assertTrue($result);
    }

    /**
     * Refunds of renewals reference the initial donation through "custom" and the renewal through "parent_txn_id".
     *
     * @since TBD
     */
    public function testRefundOfRenewalFromSameSubscriptionPasses(): void
    {
        $subscription = $this->createFeeRecoveredSubscription();
        $this->createPayPalRenewal($subscription, 'RENEWAL-TXN');

        $result = $this->verifyRefund($subscription->initialDonation()->id, 'RENEWAL-TXN', '-25.84', 'EUR');

        $this->assertTrue($result);
    }

    /**
     * @since TBD
     */
    public function testRefundOfRenewalFromAnotherSubscriptionIsRejected(): void
    {
        $subscription = $this->createFeeRecoveredSubscription();
        $otherSubscription = $this->createFeeRecoveredSubscription();
        $this->createPayPalRenewal($otherSubscription, 'OTHER-RENEWAL-TXN');

        $result = $this->verifyRefund($subscription->initialDonation()->id, 'OTHER-RENEWAL-TXN', '-25.84', 'EUR');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testRefundOfAnotherOneTimeDonationIsRejected(): void
    {
        $donation = $this->createCompletedDonation();
        $this->createCompletedDonation('OTHER-TXN');

        $result = $this->verifyRefund($donation->id, 'OTHER-TXN', '-10.00', 'USD');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testRefundWithoutParentTransactionIdIsRejected(): void
    {
        $result = $this->verifyRefund($this->createCompletedDonation()->id, '', '-10.00', 'USD');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testRefundLargerThanDonationIsRejected(): void
    {
        $result = $this->verifyRefund($this->createCompletedDonation()->id, 'INITIAL-TXN', '-10.01', 'USD');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testRefundLargerThanRenewalIsRejected(): void
    {
        $subscription = $this->createFeeRecoveredSubscription();
        $this->createPayPalRenewal($subscription, 'RENEWAL-TXN', new Money(1000, 'EUR'));

        $result = $this->verifyRefund($subscription->initialDonation()->id, 'RENEWAL-TXN', '-25.84', 'EUR');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function testRefundWithMismatchedCurrencyIsRejected(): void
    {
        $result = $this->verifyRefund($this->createCompletedDonation()->id, 'INITIAL-TXN', '-10.00', 'EUR');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     *
     * @dataProvider invalidRefundAmountProvider
     */
    public function testRefundWithInvalidAmountIsRejected(string $refundAmount): void
    {
        $result = $this->verifyRefund($this->createCompletedDonation()->id, 'INITIAL-TXN', $refundAmount, 'USD');

        $this->assertFalse($result);
    }

    /**
     * @since TBD
     */
    public function invalidRefundAmountProvider(): array
    {
        return [
            'zero'        => ['0.00'],
            'positive'    => ['10.00'],
            'non-numeric' => ['abc'],
            'empty'       => [''],
            'exponent'    => ['-1e1'],
        ];
    }

    /**
     * Mirrors the stored shape of a Fee Recovery donation: the amount includes the 0.84 fee.
     *
     * @since TBD
     */
    private function createFeeRecoveredDonation(): Donation
    {
        return Donation::factory()->create([
            'formId'             => $this->formId,
            'gatewayId'          => 'paypal',
            'status'             => DonationStatus::PENDING(),
            'amount'             => new Money(2584, 'EUR'),
            'feeAmountRecovered' => new Money(84, 'EUR'),
        ]);
    }

    /**
     * @since TBD
     */
    private function createFeeRecoveredSubscription(): Subscription
    {
        return Subscription::factory()->createWithDonation([
            'gatewayId'          => 'paypal',
            'amount'             => new Money(2584, 'EUR'),
            'feeAmountRecovered' => new Money(84, 'EUR'),
        ], [
            'formId'               => $this->formId,
            'feeAmountRecovered'   => new Money(84, 'EUR'),
            'gatewayTransactionId' => 'INITIAL-TXN',
        ]);
    }

    /**
     * @since TBD
     *
     * @param Subscription $subscription  Subscription the renewal belongs to.
     * @param string       $transactionId PayPal transaction ID of the renewal.
     * @param Money|null   $amount        Renewal amount. Defaults to the subscription amount.
     */
    private function createPayPalRenewal(Subscription $subscription, string $transactionId, ?Money $amount = null): Donation
    {
        return Subscription::factory()->createRenewal($subscription, 1, [
            'formId'               => $this->formId,
            'gatewayId'            => 'paypal',
            'gatewayTransactionId' => $transactionId,
            'amount'               => $amount ?? $subscription->amount,
        ]);
    }

    /**
     * @since TBD
     *
     * @param string $transactionId PayPal transaction ID stored on the donation.
     */
    private function createCompletedDonation(string $transactionId = 'INITIAL-TXN'): Donation
    {
        return Donation::factory()->create([
            'formId'               => $this->formId,
            'gatewayId'            => 'paypal',
            'status'               => DonationStatus::COMPLETE(),
            'amount'               => new Money(1000, 'USD'),
            'gatewayTransactionId' => $transactionId,
        ]);
    }

    /**
     * @since TBD
     *
     * @param int    $donationId   Donation ID sent in the IPN "custom" field.
     * @param string $parentTxnId  Transaction ID of the payment being refunded.
     * @param string $refundAmount IPN mc_gross value.
     * @param string $currency     IPN mc_currency value.
     */
    private function verifyRefund(int $donationId, string $parentTxnId, string $refundAmount, string $currency): bool
    {
        return $this->invokePrivateMethod('verifyEventData', [
            'payment_status' => 'Refunded',
            'receiver_email' => 'merchant@testsite.com',
            'parent_txn_id'  => $parentTxnId,
            'mc_gross'       => $refundAmount,
            'mc_currency'    => $currency,
        ], $donationId, '');
    }
}
