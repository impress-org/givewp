<?php

namespace TestsNextGen\Unit\DataTransferObjects;

use Give\DonationForms\DataTransferObjects\ValidationRouteData;
use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.0.0
 */
class ValidationRouteDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     * @throws DonationFormFieldErrorsException
     */
    public function testValidateShouldReturnValidJsonResponse()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

        $request = [
            'formId' => $form->id,
            'gatewayId' => TestGateway::id(),
            'amount' => 100,
            'currency' => "USD",
            'firstName' => "Bill",
            'lastName' => "Murray",
            'email' => "billmurray@givewp.com",
            'formTitle' => $form->title,
            'company' => null,
            'donationType' => DonationType::SINGLE()->getValue(),
            'subscriptionFrequency' => null,
            'subscriptionPeriod' => null,
            'subscriptionInstallments' => null,
        ];

        $formData = ValidationRouteData::fromRequest($request);

        $this->assertEquals($formData->validate(), new JsonResponse(['valid' => true]));
    }

    /**
     * @since 3.0.0
     * @throws DonationFormFieldErrorsException|Exception
     */
    public function testValidateShouldThrowDonationFormFieldErrorsException()
    {
        $this->expectException(DonationFormFieldErrorsException::class);

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

        $request = [
            'formId' => $form->id,
            'text_block_meta' => null
        ];

        $formData = ValidationRouteData::fromRequest($request);

        $this->assertEquals($formData->validate(), new JsonResponse(['valid' => true]));
    }

    /**
     * @since 3.0.0
     *
     * @throws DonationFormFieldErrorsException|Exception
     */
    public function testValidateShouldReturnTrueWithRecurringOptions()
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
                          {"value": "100"},
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

        $request = [
            'formId' => $form->id,
            'gatewayId' => TestGateway::id(),
            'amount' => 100,
            'currency' => "USD",
            'firstName' => "Bill",
            'lastName' => "Murray",
            'email' => "billmurray@givewp.com",
            'formTitle' => $form->title,
            'company' => null,
            'text_block_meta' => 'text block meta value',
            'donationType' => DonationType::SUBSCRIPTION()->getValue(),
            'subscriptionFrequency' => 1,
            'subscriptionPeriod' => SubscriptionPeriod::MONTH()->getValue(),
            'subscriptionInstallments' => 0,
        ];

        $formData = ValidationRouteData::fromRequest($request);

        $this->assertEquals($formData->validate(), new JsonResponse(['valid' => true]));
    }
}
