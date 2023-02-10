<?php

namespace TestsNextGen\Unit\DataTransferObjects;

use Give\Donations\ValueObjects\DonationType;
use Give\NextGen\DonationForm\DataTransferObjects\DonateControllerData;
use Give\NextGen\DonationForm\DataTransferObjects\DonateFormRouteData;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\Blocks\BlockModel;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;

/**
 * @since 0.1.0
 */
class DonateFormRouteDataTest extends TestCase
{

    /**
     * @since 0.1.0
     */
    public function testValidatedShouldReturnValidatedData()
    {
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

        $data = new DonateControllerData();

        $data->gatewayId = TestGateway::id();
        $data->amount = 100;
        $data->currency = "USD";
        $data->firstName = "Bill";
        $data->lastName = "Murray";
        $data->email = "billmurray@givewp.com";
        $data->formId = $form->id;
        $data->formTitle = $form->title;
        $data->company = null;
        $data->wpUserId = 0;
        $data->honorific = null;
        $data->text_block_meta = 'text block meta value';
        $data->donationType = DonationType::SINGLE()->getValue();
        $data->frequency = null;
        $data->period = null;
        $data->installments = null;

        $request = array_merge(get_object_vars($data), [
            'text_block_meta' => 'text block meta value'
        ]);

        $formData = DonateFormRouteData::fromRequest($request);

        $this->assertEquals($formData->validated(), $data);
    }
}
