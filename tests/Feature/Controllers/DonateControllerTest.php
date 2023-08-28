<?php

namespace Give\Tests\Feature\Controllers;

use Exception;
use Give\DonationForms\Controllers\DonateController;
use Give\DonationForms\DataTransferObjects\DonateFormRouteData;
use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class DonateControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
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
            'name' => 'givewp/section',
            'attributes' => ['title' => '', 'description' => ''],
            'innerBlocks' => [
                [
                    'name' => 'givewp/text',
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
