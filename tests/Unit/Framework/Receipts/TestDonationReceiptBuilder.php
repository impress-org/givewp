<?php

namespace Give\Tests\Unit\Framework\Receipts;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Receipts\DonationReceiptBuilder;
use Give\Framework\Receipts\Properties\ReceiptDetail;
use Give\Framework\Receipts\Properties\ReceiptDetailCollection;
use Give\Framework\TemplateTags\DonationTemplateTags;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonationReceiptBuilder extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     */
    public function testToConfirmationPageShouldReturnDonationReceipt()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
        ]);

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
                ['amount' => $donation->amount->formatToDecimal()]
            ),
            new ReceiptDetail(
                __('Donation Total', 'give'),
                ['amount' => $donation->amount->formatToDecimal()]
            ),
        ]);

        $additionalDetails = new ReceiptDetailCollection();

        if ($donation->company) {
            $additionalDetails->addDetail(
                new ReceiptDetail(
                    __('Company Name', 'give'),
                    $receipt->donation->company
                )
            );
        }


        if ($donation->comment) {
            $additionalDetails->addDetail(
                new ReceiptDetail(
                    __('Comment', 'give'),
                    $receipt->donation->comment
                )
            );
        }

        if ($receipt->donation->anonymous) {
            $additionalDetails->addDetail(
                new ReceiptDetail(
                    __('Anonymous Donation', 'give'),
                    'Yes'
                )
            );
        }

        $heading = (new DonationTemplateTags($donation, $donationForm->settings->receiptHeading))->getContent();
        $description = (new DonationTemplateTags($donation, $donationForm->settings->receiptDescription))->getContent();

        $settings = [
            'heading' => $heading,
            'description' => $description,
            'currency' => $receipt->donation->amount->getCurrency()->getCode(),
            'donorDashboardUrl' => get_permalink(give_get_option('donor_dashboard_page')),
            'pdfReceiptLink' => '',
        ];


        $this->assertSame(
            $receiptBuilder->toConfirmationPage()->toArray(),
            [
                'settings' => $settings,
                'donorDetails' => $donorDetails->toArray(),
                'donationDetails' => $donationDetails->toArray(),
                'eventTicketsDetails' => [],
                'subscriptionDetails' => [],
                'additionalDetails' => $additionalDetails->toArray(),
            ]
        );
    }

}
