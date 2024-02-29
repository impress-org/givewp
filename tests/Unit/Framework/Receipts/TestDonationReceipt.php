<?php

namespace Give\Tests\Unit\Framework\Receipts;

use Give\DonationForms\Listeners\StoreCustomFields;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Receipts\Properties\ReceiptDetail;
use Give\Framework\Receipts\Properties\ReceiptDetailCollection;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonationReceipt extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     */
    public function testToArrayReturnsExpectedArrayShape()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();

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

        $receipt = new DonationReceipt($donation);
        $receipt->settings->addSetting('currency', $receipt->donation->amount->getCurrency()->getCode());
        $receipt->settings->addSetting('donorDashboardUrl', get_permalink(give_get_option('donor_dashboard_page')));
        $receipt->settings->addSetting('pdfReceiptLink', '');
        $receipt->donorDetails->addDetails($donorDetails->getDetails());
        $receipt->donationDetails->addDetails($donationDetails->getDetails());

        $this->assertSame(
            $receipt->toArray(),
            [
                'settings' => [
                    'currency' => $receipt->donation->amount->getCurrency()->getCode(),
                    'donorDashboardUrl' => get_permalink(give_get_option('donor_dashboard_page')),
                    'pdfReceiptLink' => '',
                ],
                'donorDetails' => $donorDetails->toArray(),
                'donationDetails' => $donationDetails->toArray(),
                'eventTicketsDetails' => [],
                'subscriptionDetails' => [],
                'additionalDetails' => [],
            ]
        );
    }

    /**
     * @since 3.0.0
     */
    public function testToArrayShouldBeEmptyWithoutGenerate()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();

        $receipt = new DonationReceipt($donation);

        $this->assertSame(
            $receipt->toArray(),
            [
                'settings' => [],
                'donorDetails' => [],
                'donationDetails' => [],
                'eventTicketsDetails' => [],
                'subscriptionDetails' => [],
                'additionalDetails' => [],
            ]
        );
    }

    /**
     * @since 3.0.0
     */
    public function testToArrayReturnsExpectedArrayShapeWithSubscriptionDetails()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $donation = $subscription->initialDonation();
        $receipt = new DonationReceipt($donation);

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

        $receipt->subscriptionDetails->addDetails($subscriptionDetails->getDetails());

        $this->assertSame(
            $receipt->toArray()['subscriptionDetails'],
            $subscriptionDetails->toArray()
        );
    }

    public function testToArrayDonationDetailsShouldDisplayFeeRecovered()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'feeAmountRecovered' => Money::fromDecimal(10, 'USD'),
        ]);

        $receipt = new DonationReceipt($donation);

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
                $donation->intendedAmount()->formatToDecimal()
            ),
            new ReceiptDetail(
                __('Processing Fee', 'give'),
                $donation->feeAmountRecovered->formatToDecimal()
            ),
            new ReceiptDetail(
                __('Donation Total', 'give'),
                $donation->amount->formatToDecimal()
            ),
        ]);

        $receipt->donationDetails->addDetails($donationDetails->getDetails());

        $this->assertSame(
            $receipt->toArray()['donationDetails'],
            $donationDetails->toArray()
        );
    }

    /**
     * @since 3.0.0
     */
    public function testAddAdditionalDetailWithToArrayReturnsExpectedArrayShape()
    {
        /** @var Donation $donation */
        $donation = Donation::factory()->create();

        $receipt = new DonationReceipt($donation);

        $additionalDetails = new ReceiptDetailCollection([
            new ReceiptDetail(
                __('Additional Detail', 'give'),
                'Additional Detail Value'
            ),
        ]);

        $receipt->additionalDetails->addDetails($additionalDetails->getDetails());

        $this->assertSame(
            $receipt->toArray()['additionalDetails'],
            $additionalDetails->toArray()
        );
    }

    /**
     * @since 3.0.0
     */
    public function testToArrayReturnsExpectedArrayShapeWithCustomFields()
    {
        $donationForm = $this->createFormWithCustomFields(
            [
                BlockModel::make([
                    'name' => 'givewp/section',
                    'attributes' => ['title' => '', 'description' => ''],
                    'innerBlocks' => [
                        [
                            'name' => 'givewp/text',
                            'attributes' => [
                                'fieldName' => 'custom_text_block_meta',
                                'storeAsDonorMeta' => false,
                                'storeAsDonationMeta' => true,
                                'displayInAdmin' => true,
                                'displayInReceipt' => true,
                                'label' => 'Custom Text Field',
                                'description' => ''
                            ],
                        ]
                    ]
                ])
            ]
        );

        /** @var Donation $donation */
        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
        ]);

        (new StoreCustomFields())($donationForm, $donation, null, ['custom_text_block_meta' => 'Custom Text Block Value']);

        $receipt = new DonationReceipt($donation);

        $additionalDetails = new ReceiptDetailCollection([
            new ReceiptDetail(
                __('Custom Text Field', 'give'),
                'Custom Text Block Value'
            ),
        ]);

        $receipt->additionalDetails->addDetails($additionalDetails->getDetails());

        $this->assertSame(
            $receipt->toArray()['additionalDetails'],
            $additionalDetails->toArray()
        );
    }

    /**
     * BlockModel[] $blocks
     *
     */
    protected function createFormWithCustomFields($blocks = []): DonationForm
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $customBlocks = $blocks ?: [
            BlockModel::make([
                'name' => 'givewp/section',
                'attributes' => ['title' => '', 'description' => ''],
                'innerBlocks' => [
                    [
                        'name' => 'givewp/text',
                        'attributes' => [
                            'fieldName' => 'custom_text_block_meta',
                            'storeAsDonorMeta' => false,
                            'storeAsDonationMeta' => true,
                            'displayInAdmin' => true,
                            'displayInReceipt' => true,
                            'label' => 'Custom Text Field',
                            'description' => ''
                        ],
                    ]
                ]
            ])
        ];

        $form->blocks = BlockCollection::make(
            array_merge($form->blocks->getBlocks(), $customBlocks)
        );

        $form->save();

        return DonationForm::find($form->id);
    }
}
