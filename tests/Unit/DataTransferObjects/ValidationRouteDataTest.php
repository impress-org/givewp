<?php

namespace TestsNextGen\Unit\DataTransferObjects;

use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\JsonResponse;
use Give\NextGen\DonationForm\DataTransferObjects\ValidationRouteData;
use Give\NextGen\DonationForm\Exceptions\DonationFormFieldErrorsException;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\NextGen\Framework\Blocks\BlockCollection;
use Give\NextGen\Framework\Blocks\BlockModel;
use Give\NextGen\Gateways\NextGenTestGateway\NextGenTestGateway;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class ValidationRouteDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @throws DonationFormFieldErrorsException
     */
    public function testValidateShouldReturnValidJsonResponse()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $request = [
            'formId' => $form->id,
            'gatewayId' => NextGenTestGateway::id(),
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
     * @unreleased
     * @throws DonationFormFieldErrorsException|Exception
     */
    public function testValidateShouldThrowDonationFormFieldErrorsException()
    {
        $this->expectException(DonationFormFieldErrorsException::class);

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

        $request = [
            'formId' => $form->id,
            'text_block_meta' => null
        ];

        $formData = ValidationRouteData::fromRequest($request);

        $this->assertEquals($formData->validate(), new JsonResponse(['valid' => true]));
    }

    /**
     * @unreleased
     *
     * @throws DonationFormFieldErrorsException|Exception
     */
    public function testValidateShouldReturnTrueWithRecurringOptions()
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

        $amountBlockDataWithRecurringEnabled = json_decode(
            '{
                  "clientId": "8371d4c7-0e8d-4aff-a1a1-b4520f008132",
                  "name": "custom-block-editor/section",
                  "isValid": true,
                  "attributes": {
                    "title": "How much would you like to donate today?",
                    "description": "All donations directly impact our organization and help us further our mission."
                  },
                  "innerBlocks": [
                    {
                      "clientId": "bddaa0ea-29bf-4143-b62d-aae3396e9b0f",
                      "name": "custom-block-editor/donation-amount-levels",
                      "isValid": true,
                      "attributes": {
                        "label": "Donation Amount",
                        "levels": [
                          "10",
                          "25",
                          "50",
                          "100",
                          "250",
                          "500"
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
            'gatewayId' => NextGenTestGateway::id(),
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
