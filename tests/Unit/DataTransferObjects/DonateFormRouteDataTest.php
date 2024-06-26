<?php

namespace TestsNextGen\Unit\DataTransferObjects;

use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\DonationForms\DataTransferObjects\DonateFormRouteData;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class DonateFormRouteDataTest extends TestCase
{

    /**
     * @since 3.0.0
     */
    public function testValidatedShouldReturnValidatedData()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

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
        $data->donationType = DonationType::SINGLE();
        $data->subscriptionFrequency = null;
        $data->subscriptionPeriod = null;
        $data->subscriptionInstallments = null;
        $data->country = null;
        $data->address1 = null;
        $data->address2 = null;
        $data->city = null;
        $data->state = null;
        $data->zip = null;

        $request = array_merge(get_object_vars($data), [
            'text_block_meta' => 'text block meta value',
            'donationType' => $data->donationType->getValue(),
        ]);

        $formData = DonateFormRouteData::fromRequest($request);

        $this->assertEquals($formData->validated(), $data);
    }

    /**
     * @since 3.0.0
     */
    public function testValidatedShouldReturnValidatedDataWithSubscriptionData()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

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

        $amountBlockDataWithRecurringEnabled = json_decode(
            '{
                  "clientId": "8371d4c7-0e8d-4aff-a1a1-b4520f008132",
                  "name": "givewp/section",
                  "isValid": true,
                  "attributes": {
                    "title": "How much would you like to donate today?",
                    "description": "All donations directly impact our organization and help us further our mission."
                  },
                  "innerBlocks": [
                    {
                      "clientId": "bddaa0ea-29bf-4143-b62d-aae3396e9b0f",
                      "name": "givewp/donation-amount",
                      "isValid": true,
                      "attributes": {
                        "label": "Donation Amount",
                        "levels": [
                          {"value": "10"},
                          {"value": "25"},
                          {"value": "50"},
                          {"value": "100", "checked": true},
                          {"value": "250"},
                          {"value": "500"}
                        ],
                        "priceOption": "multi",
                        "setPrice": "100",
                        "customAmount": "true",
                        "customAmountMin": 1,
                        "recurringBillingPeriodOptions": [
                          "month"
                        ],
                        "recurringBillingPeriod": "month",
                        "recurringBillingInterval": 1,
                        "recurringDonationChoice": "admin",
                        "recurringEnabled": true,
                        "recurringLengthOfTime": "0",
                        "recurringOptInDefaultBillingPeriod": "month",
                        "lock": {
                          "remove": true
                        }
                      },
                      "innerBlocks": []
                    }
                  ]
                }',
            true
        );

        $amountBlock = BlockModel::make($amountBlockDataWithRecurringEnabled);

        $modifiedFormBlocks = $form->blocks->getBlocks();
        unset($modifiedFormBlocks[0]);

        $blocks = array_merge(
            [
                $amountBlock,
                $customFieldBlockModel,
            ],
            $modifiedFormBlocks
        );

        $form->blocks = BlockCollection::make($blocks);

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
        $data->donationType = DonationType::SUBSCRIPTION();
        $data->subscriptionFrequency = 1;
        $data->subscriptionPeriod = SubscriptionPeriod::MONTH();
        $data->subscriptionInstallments = 0;
        $data->country = null;
        $data->address1 = null;
        $data->address2 = null;
        $data->city = null;
        $data->state = null;
        $data->zip = null;

        $requestData = get_object_vars($data);

        $request = array_merge($requestData, [
            'text_block_meta' => 'text block meta value',
            'donationType' => $data->donationType->getValue(),
            'subscriptionPeriod' => $data->subscriptionPeriod->getValue(),
        ]);

        $formData = DonateFormRouteData::fromRequest($request);
        $validData = $formData->validated();

        $this->assertEquals($validData, $data);
    }
}
