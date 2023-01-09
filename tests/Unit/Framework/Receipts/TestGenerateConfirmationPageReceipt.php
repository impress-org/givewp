<?php

namespace Give\NextGen\Framework\Receipts;

use Give\Donations\Models\Donation;
use Give\NextGen\Framework\Receipts\Actions\GenerateConfirmationPageReceipt;
use Give\NextGen\Framework\Receipts\Properties\ReceiptDetail;
use Give\NextGen\Framework\Receipts\Properties\ReceiptDetailCollection;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestGenerateConfirmationPageReceipt extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testShouldGenerateReceiptForOneTimeDonation()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();

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
                $donation->amount->formatToDecimal()
            ),
            new ReceiptDetail(
                __('Donation Total', 'give'),
                $donation->amount->formatToDecimal()
            ),
        ]);

        $this->assertSame(
            $receipt->toArray(),
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

    /**
     * @unreleased
     */
    public function testShouldGenerateReceiptForRecurringDonation()
    {
        $subscription = Subscription::factory()->createWithDonation();
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

        $subscriptionDetails = new ReceiptDetailCollection([
            new ReceiptDetail(
                __('Subscription', 'give'),
                sprintf(
                    '%s / %s',
                    $subscription->amount->formatToDecimal(),
                    $subscription->period->getValue()
                )
            ),
            new ReceiptDetail(
                __('Subscription Status', 'give'),
                $subscription->status->getValue()
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

        $this->assertSame(
            $receipt->toArray(),
            [
                'settings' => [
                    'currency' => $receipt->donation->amount->getCurrency()->getCode(),
                    'donorDashboardUrl' => get_permalink(give_get_option('donor_dashboard_page')),
                ],
                'donorDetails' => $donorDetails->toArray(),
                'donationDetails' => $donationDetails->toArray(),
                'subscriptionDetails' => $subscriptionDetails->toArray(),
                'additionalDetails' => [],
            ]
        );
    }
}