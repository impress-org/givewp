<?php

namespace Give\Tests\Unit\Framework\Receipts;

use Give\Donations\Models\Donation;
use Give\NextGen\Framework\Receipts\DonationReceipt;
use Give\NextGen\Framework\Receipts\DonationReceiptBuilder;
use Give\NextGen\Framework\Receipts\Properties\ReceiptDetail;
use Give\NextGen\Framework\Receipts\Properties\ReceiptDetailCollection;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonationReceiptBuilder extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testToConfirmationPageShouldReturnDonationReceipt()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();

        $receipt = new DonationReceipt($donation);
        $receiptBuilder = new DonationReceiptBuilder($receipt);

        $donorDetails = new ReceiptDetailCollection([
            new ReceiptDetail(
                __('Donor Name', 'give'),
                trim("{$donation->firstName} {$donation->lastName}")
            ),
            new ReceiptDetail(
                __('Email Address', 'give'),
                $donation->email
            ),
        ]);

        $donationDetails = new ReceiptDetailCollection([
            new ReceiptDetail(
                __('Payment Status', 'give'),
                give_get_payment_statuses()[$donation->status->getValue()]
            ),
            new ReceiptDetail(
                __('Payment Method', 'give'),
                $donation->gateway()->getPaymentMethodLabel()
            ),
            new ReceiptDetail(
                __('Donation Amount', 'give'),
                $donation->amount->formatToDecimal()
            ),
            new ReceiptDetail(
                __('Donation Total', 'give'),
                $donation->amount->formatToDecimal()
            ),
        ]);

        $this->assertSame(
            $receiptBuilder->toConfirmationPage()->toArray(),
            [
                'settings' => [
                    'currency' => $receipt->donation->amount->getCurrency()->getCode(),
                    'donorDashboardUrl' => get_permalink(give_get_option('donor_dashboard_page')),
                ],
                'donorDetails' => $donorDetails->toArray(),
                'donationDetails' => $donationDetails->toArray(),
                'subscriptionDetails' => [],
                'additionalDetails' => [],
            ]
        );
    }

}