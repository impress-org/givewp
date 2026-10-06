<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalStandard\Webhooks\Listeners;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\Listeners\PaymentUpdated;
use Give\Tests\TestCase;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.18.0.1
 */
class PaymentUpdatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var int
     */
    private $formId;

    public function setUp(): void
    {
        parent::setUp();

        $this->formId = DonationForm::factory()->create()->id;
    }

    /**
     * @since 4.18.0.1
     */
    public function testCompletedPaymentCompletesDonation(): void
    {
        $donation = $this->createDonation(DonationStatus::PENDING());

        (new PaymentUpdated())->processEvent($this->completedEvent($donation->id, 'NEW-TXN'));

        $donation = Donation::find($donation->id);
        $this->assertTrue($donation->status->isComplete());
        $this->assertSame('NEW-TXN', $donation->gatewayTransactionId);
    }

    /**
     * @since 4.18.0.1
     */
    public function testTransactionIdRecordedOnAnotherDonationIsRejected(): void
    {
        $this->createDonation(DonationStatus::COMPLETE(), 'USED-TXN');
        $donation = $this->createDonation(DonationStatus::PENDING());

        (new PaymentUpdated())->processEvent($this->completedEvent($donation->id, 'USED-TXN'));

        $this->assertTrue(Donation::find($donation->id)->status->isPending());
    }

    /**
     * @since 4.18.0.1
     */
    public function testMissingTransactionIdIsRejected(): void
    {
        $donation = $this->createDonation(DonationStatus::PENDING());

        (new PaymentUpdated())->processEvent($this->completedEvent($donation->id, ''));

        $this->assertTrue(Donation::find($donation->id)->status->isPending());
    }

    /**
     * @since 4.18.0.1
     */
    public function testFullRefundOfRenewalRefundsTheRenewalOnly(): void
    {
        $subscription = Subscription::factory()->createWithDonation(['amount' => new Money(1000, 'USD')]);
        $initialDonation = $subscription->initialDonation();
        $initialDonation->status = DonationStatus::COMPLETE();
        $initialDonation->gatewayTransactionId = 'INITIAL-TXN';
        $initialDonation->save();
        $renewal = Subscription::factory()->createRenewal($subscription, 1, [
            'formId'               => $this->formId,
            'gatewayId'            => 'paypal',
            'gatewayTransactionId' => 'RENEWAL-TXN',
            'amount'               => new Money(1000, 'USD'),
        ]);

        (new PaymentUpdated())->processEvent($this->refundEvent($initialDonation->id, 'RENEWAL-TXN', '-10.00'));

        $this->assertTrue(Donation::find($renewal->id)->status->isRefunded());
        $this->assertTrue(Donation::find($initialDonation->id)->status->isComplete());
    }

    /**
     * @since 4.18.0.1
     */
    public function testRefundOfInitialDonationRefundsTheInitialDonation(): void
    {
        $donation = $this->createDonation(DonationStatus::COMPLETE(), 'INITIAL-TXN');

        (new PaymentUpdated())->processEvent($this->refundEvent($donation->id, 'INITIAL-TXN', '-' . $donation->amount->formatToDecimal()));

        $this->assertTrue(Donation::find($donation->id)->status->isRefunded());
    }

    /**
     * @since 4.18.0.1
     *
     * @param DonationStatus $status        Status of the PayPal Standard donation.
     * @param string         $transactionId PayPal transaction ID stored on the donation.
     */
    private function createDonation(DonationStatus $status, string $transactionId = ''): Donation
    {
        return Donation::factory()->create([
            'formId'               => $this->formId,
            'gatewayId'            => 'paypal',
            'status'               => $status,
            'gatewayTransactionId' => $transactionId,
        ]);
    }

    /**
     * @since 4.18.0.1
     *
     * @param int    $donationId    Donation ID sent in the IPN "custom" field.
     * @param string $transactionId IPN txn_id value.
     */
    private function completedEvent(int $donationId, string $transactionId): object
    {
        return (object) [
            'custom'         => $donationId,
            'payment_status' => 'Completed',
            'txn_id'         => $transactionId,
        ];
    }

    /**
     * @since 4.18.0.1
     *
     * @param int    $donationId    Donation ID sent in the IPN "custom" field.
     * @param string $parentTxnId   IPN parent_txn_id value.
     * @param string $amount        IPN mc_gross value.
     */
    private function refundEvent(int $donationId, string $parentTxnId, string $amount): object
    {
        return (object) [
            'custom'         => $donationId,
            'payment_status' => 'Refunded',
            'txn_id'         => 'REFUND-TXN',
            'parent_txn_id'  => $parentTxnId,
            'reason_code'    => 'refund',
            'mc_gross'       => $amount,
        ];
    }
}
