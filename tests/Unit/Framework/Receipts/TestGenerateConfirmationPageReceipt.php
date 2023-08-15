<?php

namespace Give\Framework\Receipts;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Framework\Receipts\Actions\GenerateConfirmationPageReceipt;
use Give\Framework\Receipts\Properties\ReceiptDetail;
use Give\Framework\Receipts\Properties\ReceiptDetailCollection;
use Give\Framework\TemplateTags\DonationTemplateTags;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestGenerateConfirmationPageReceipt extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 0.1.0
     */
    public function testShouldGenerateReceiptForOneTimeDonation()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'formId' => $donationForm->id
        ]);

        $initialReceipt = new DonationReceipt($donation);

        $receipt = (new GenerateConfirmationPageReceipt())($initialReceipt);

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
        ];

        $this->assertSame(
            $receipt->toArray(),
            [
                'settings' => $settings,
                'donorDetails' => $donorDetails->toArray(),
                'donationDetails' => $donationDetails->toArray(),
                'subscriptionDetails' => [],
                'additionalDetails' => $additionalDetails->toArray(),
            ]
        );
    }

    /**
     * @since 0.1.0
     */
    public function testShouldGenerateReceiptForRecurringDonation()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();

        $subscription = Subscription::factory()->createWithDonation(['donationFormId' => $donationForm->id]);
        $donation = $subscription->initialDonation();

        $initialReceipt = new DonationReceipt($donation);

        $receipt = (new GenerateConfirmationPageReceipt())($initialReceipt);

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
                $donation->status->label()
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

        $subscriptionAmountLabel = sprintf(
            $subscription->period->label($subscription->frequency),
            $subscription->frequency
        );

        $subscriptionDetails = new ReceiptDetailCollection([
            new ReceiptDetail(
                __('Subscription', 'give'),
                [
                    'amount' =>
                        sprintf(
                            '%s / %s',
                            $subscription->amount->formatToDecimal(),
                            $subscriptionAmountLabel
                        )
                ]
            ),
            new ReceiptDetail(
                __('Subscription Status', 'give'),
                $subscription->status->label()
            ),
            new ReceiptDetail(
                __('Renewal Date', 'give'),
                $subscription->renewsAt->format('F j, Y')
            ),
            new ReceiptDetail(
                __('Progress', 'give'),
                sprintf(
                    '%s / %s',
                    count($subscription->donations),
                    $subscription->installments > 0 ? $subscription->installments : 'Ongoing'
                )
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

        if ($donation->anonymous) {
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
        ];

        $this->assertSame(
            $receipt->toArray(),
            [
                'settings' => $settings,
                'donorDetails' => $donorDetails->toArray(),
                'donationDetails' => $donationDetails->toArray(),
                'subscriptionDetails' => $subscriptionDetails->toArray(),
                'additionalDetails' => $additionalDetails->toArray(),
            ]
        );
    }
}
