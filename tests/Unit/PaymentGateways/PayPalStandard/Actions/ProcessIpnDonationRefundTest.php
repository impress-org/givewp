<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalStandard\Actions;

use Give\Helpers\Call;
use Give\PaymentGateways\Gateways\PayPalStandard\Actions\ProcessIpnDonationRefund;
use Give_Payment;
use PHPUnit\Framework\TestCase;

/**
 * @since 2.19.0
 */
class ProcessIpnDonationRefundTest extends TestCase
{
    /**
     * @var Give_Payment
     */
    private $donation;

    /**
     * @var MockProcessIpnDonationRefund
     */
    private $processIpnDonationRefund;

    protected function setUp(): void
    {
        parent::setUp();
        $this->donation = new Give_Payment(\Give_Helper_Payment::create_simple_payment());
        $this->processIpnDonationRefund = new MockProcessIpnDonationRefund();
    }

    public function testIsPartialRefundFunction()
    {
        $this->assertFalse(
            $this->processIpnDonationRefund->mockIsPartialRefund(
                '20.00',
                $this->donation->currency,
                $this->donation->total
            )
        );

        $this->assertTrue(
            $this->processIpnDonationRefund->mockIsPartialRefund(
                '19.89',
                $this->donation->currency,
                $this->donation->total
            )
        );
    }

    public function testDonationStatusRemainCompletedOnPartialRefund()
    {
        $this->donation->status = 'publish';
        $this->donation->save();

        Call::invoke(
            MockProcessIpnDonationRefund::class,
            (object) [
                'mc_gross' => '-19.05', // Actual donation amount is 20.00
                'parent_txn_id' => 'abc123',
            ],
            $this->donation->ID
        );

        $notes = give_get_payment_notes($this->donation->ID);
        $notes = array_filter($notes, function ($note) {
            return false !== strpos($note->comment_content, 'Partial PayPal refund processed');
        });

        $this->assertTrue((bool)count($notes));
        $this->assertSame('publish', get_post_status( $this->donation->ID ));
    }

    public function testDonationStatusUpdateToRefundedOnFullDonationAmountRefund()
    {
        $this->donation->status = 'publish';
        $this->donation->save();

        Call::invoke(
            MockProcessIpnDonationRefund::class,
            (object) [
                'mc_gross' => '-20.00',
                'parent_txn_id' => 'abc456',
                'reason_code' => 'abc',
                'txn_id' => 'abs123'
            ],
            $this->donation->ID
        );

        $notes = give_get_payment_notes($this->donation->ID);
        $notes = array_filter($notes, function ($note) {
            return false !== strpos($note->comment_content, 'PayPal Refund Transaction ID');
        });

        $this->assertTrue((bool)count($notes));
        $this->assertSame('refunded', get_post_status( $this->donation->ID ));
    }

    /**
     * @since TBD
     *
     * @dataProvider invalidRefundAmountProvider
     */
    public function testInvalidRefundAmountLeavesDonationUnchanged(array $refundAmountData)
    {
        $this->donation->status = 'publish';
        $this->donation->save();

        Call::invoke(
            MockProcessIpnDonationRefund::class,
            (object) array_merge([
                'parent_txn_id' => 'INVALID-AMOUNT-PARENT-TXN',
                'reason_code' => 'refund',
                'txn_id' => 'INVALID-AMOUNT-TXN',
            ], $refundAmountData),
            $this->donation->ID
        );

        $notes = give_get_payment_notes($this->donation->ID);
        $notes = array_filter($notes, function ($note) {
            return false !== strpos($note->comment_content, 'INVALID-AMOUNT-');
        });

        $this->assertSame([], array_column($notes, 'comment_content'));
        $this->assertSame('publish', get_post_status($this->donation->ID));
    }

    /**
     * @since TBD
     */
    public function invalidRefundAmountProvider(): array
    {
        return [
            'missing'     => [[]],
            'non-numeric' => [['mc_gross' => 'abc']],
            'zero'        => [['mc_gross' => '0.00']],
            'positive'    => [['mc_gross' => '5.00']],
            'over total'  => [['mc_gross' => '-999999.00']],
        ];
    }
}

class MockProcessIpnDonationRefund extends ProcessIpnDonationRefund
{
    public function mockIsPartialRefund($refundedAmount, $currency, $donationAmount)
    {
        return $this->isPartialRefund($refundedAmount, $currency, $donationAmount);
    }
}
