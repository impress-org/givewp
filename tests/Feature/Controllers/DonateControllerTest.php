<?php

namespace Give\Tests\Feature\Controllers;

use Exception;
use Give\Donations\ValueObjects\DonationType;
use Give\NextGen\DonationForm\Controllers\DonateController;
use Give\NextGen\DonationForm\DataTransferObjects\DonateFormRouteData;
use Give\NextGen\DonationForm\Exceptions\DonationFormFieldErrorsException;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\Blocks\BlockModel;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class DonateControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 0.1.0
     * @return void
     * @throws Exception|DonationFormFieldErrorsException
     */
    public function testShouldThrowExceptionWhenCustomFieldIsRequiredAndEmpty()
    {
        $this->expectException(DonationFormFieldErrorsException::class);

        $testGateway = new TestGateway();

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $customFieldBlockModel = BlockModel::make([
            'name' => 'custom-block-editor/section',
            'attributes' => ['title' => '', 'description' => ''],
            'innerBlocks' => [
                [
                    'name' => 'custom-block-editor/custom-text-block',
                    'attributes' => [
                        'fieldName' => 'text_block_meta',
                        'title' => 'Custom Text Field',
                        'description' => '',
                        'isRequired' => true
                    ],
                ]
            ]
        ]);

        $form->blocks = BlockCollection::make(
            array_merge([$customFieldBlockModel], $form->blocks->getBlocks())
        );

        $form->save();

        $formData = DonateFormRouteData::fromRequest([
            'gatewayId' => $testGateway::id(),
            'amount' => 50,
            'currency' => 'USD',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'bill@murray.com',
            'formId' => $form->id,
            'company' => null,
            'honorific' => null,
            'originUrl' => 'https://givewp.com',
            'embedId' => '123',
            'isEmbed' => true,
            'donationType' => DonationType::SINGLE()->getValue(),
            'subscriptionFrequency' => null,
            'subscriptionPeriod' => null,
            'subscriptionInstallments' => null,
            'text_block_meta' => ''
        ]);

        $donateController = new DonateController();

        $donateController->donate($formData->validated(), $testGateway);
    }
}
