<?php

namespace Give\Tests\Unit\DonationForm\VieModels;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\DonationForm\Repositories\DonationFormRepository;
use Give\NextGen\DonationForm\ViewModels\DonationConfirmationReceiptViewModel;
use Give\NextGen\Framework\Receipts\DonationReceipt;
use Give\NextGen\Framework\Receipts\DonationReceiptBuilder;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class DonationConfirmationReceiptViewModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 0.1.0
     */
    public function testFormExportsShouldReturnExpectedArrayOfData()
    {
        /** @var DonationForm $donationForm */
        $donationForm = DonationForm::factory()->create();
        $donationFormRepository = new DonationFormRepository((new PaymentGatewayRegister));
        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
        ]);

        $formDataGateways = $donationFormRepository->getFormDataGateways($donationForm->id);
        $formApi = $donationFormRepository->getFormSchemaFromBlocks(
            $donationForm->id,
            $donationForm->blocks
        )->jsonSerialize();

        $viewModel = new DonationConfirmationReceiptViewModel($donation);

        $this->assertEquals($viewModel->formExports(), [
            'registeredGateways' => $formDataGateways,
            'form' => $formApi,
        ]);
    }

    /**
     * @since 0.1.0
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
     * @since 0.1.0
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
     * @since 0.1.0
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
