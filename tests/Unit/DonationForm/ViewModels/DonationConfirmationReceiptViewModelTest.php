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
     * @unreleased
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
            'gatewaySettings' => $formDataGateways,
            'form' => $formApi,
        ]);
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
