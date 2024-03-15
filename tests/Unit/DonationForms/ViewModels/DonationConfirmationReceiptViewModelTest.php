<?php

namespace Give\Tests\Unit\DonationForms\ViewModels;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\ViewModels\DonationConfirmationReceiptViewModel;
use Give\Donations\Models\Donation;
use Give\Framework\Receipts\DonationReceipt;
use Give\Framework\Receipts\DonationReceiptBuilder;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class DonationConfirmationReceiptViewModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     */
    public function testFormExportsShouldReturnExpectedArrayOfData()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $donationFormRepository = give(DonationFormRepository::class);
        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
        ]);

        $formDataGateways = $donationFormRepository->getFormDataGateways($donationForm->id);
        $formApi = $donationFormRepository->getFormSchemaFromBlocks(
            $donationForm->id,
            $donationForm->blocks
        )->jsonSerialize();

        $viewModel = new DonationConfirmationReceiptViewModel($donation);

        $this->assertEquals([
            'registeredGateways' => $formDataGateways,
            'form' => $formApi,
        ], $viewModel->formExports());
    }

    /**
     * @since 3.0.0
     */
    public function testExportsShouldReturnExpectedArrayOfData()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
        ]);

        $receipt = new DonationReceipt($donation);
         $receipt->settings->addSetting(
            'heading',
            __("Hey {$donation->firstName}, thanks for your donation!", 'give')
        );
        $receipt->settings->addSetting(
            'description',
            __(
                "{$donation->firstName}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {$donation->email}.",
                'give'
            )
        );
        $receiptBuilder = new DonationReceiptBuilder($receipt);

        $viewModel = new DonationConfirmationReceiptViewModel($donation);

        $this->assertEquals($viewModel->exports(), [
            'receipt' => $receiptBuilder->toConfirmationPage()->toArray(),
        ]);
    }

    /**
     * @since 3.0.0
     */
    public function testGetReceiptShouldReturnConfirmationReceipt()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
        ]);

        $viewModel = new DonationConfirmationReceiptViewModel($donation);

        $receipt = new DonationReceipt($donation);
         $receipt->settings->addSetting(
            'heading',
            __("Hey {$donation->firstName}, thanks for your donation!", 'give')
        );
        $receipt->settings->addSetting(
            'description',
            __(
                "{$donation->firstName}, your contribution means a lot and will be put to good use in making a difference. We’ve sent your donation receipt to {$donation->email}.",
                'give'
            )
        );
        $receiptBuilder = new DonationReceiptBuilder($receipt);

        $this->assertSame($receiptBuilder->toConfirmationPage()->toArray(), $viewModel->getReceipt()->toArray());
    }

    /**
     * @since 3.0.0
     */
    public function testGetDonationFormShouldReturnModel()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
        ]);

        $viewModel = new DonationConfirmationReceiptViewModel($donation);

        $this->assertEquals($donationForm->getAttributes(), $viewModel->getDonationForm()->getAttributes());
    }
}
