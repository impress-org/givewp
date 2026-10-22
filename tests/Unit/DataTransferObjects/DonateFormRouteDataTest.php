<?php

namespace TestsNextGen\Unit\DataTransferObjects;

use Exception;
use Give\DonationForms\DataTransferObjects\DonateControllerData;
use Give\DonationForms\DataTransferObjects\DonateFormRouteData;
use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\DonationForms\Exceptions\DonationFormForbidden;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\FieldsAPI\DonationForm as DonationFormNode;
use Give\Framework\FieldsAPI\Properties\DonationForm\CurrencySwitcherSetting;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Tests\TestCase;

/**
 * @since 3.0.0
 */
class DonateFormRouteDataTest extends TestCase
{

    /**
     * @since 3.17.0 updated to ignore honeypot field
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

        add_filter("givewp_donation_forms_honeypot_enabled", "__return_false");

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
     * @since 4.19.0.1
     */
    public function testValidatedShouldRejectCurrencyThatDoesNotMatchFormCurrency()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $formCurrency = give_get_currency($form->id);
        $otherCurrency = current(array_diff(array_keys(give_get_currencies_list()), [$formCurrency]));

        $this->assertIsString($otherCurrency);

        try {
            DonateFormRouteData::fromRequest($this->donationRequestData($form, $otherCurrency))->validated();
            $this->fail('Expected a validation error for a currency the form does not offer.');
        } catch (DonationFormFieldErrorsException $exception) {
            $errors = $exception->getError()->get_error_messages('currency');

            $this->assertNotEmpty($errors);
            $this->assertStringContainsString('must be a currency supported by this donation form', $errors[0]);
        }
    }

    /**
     * @since 4.19.0.1
     */
    public function testValidatedShouldAcceptCurrencyEnabledByCurrencySwitcher(): void
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        add_action('givewp_donation_form_schema', static function (DonationFormNode $schema) {
            $schema->currencySwitcherSettings(
                new CurrencySwitcherSetting('USD', 1, [], 2),
                new CurrencySwitcherSetting('EUR', 2, [], 2)
            );

            $schema->getNodeByName('currency')->defaultValue('EUR');
        });

        $data = DonateFormRouteData::fromRequest($this->donationRequestData($form, 'EUR'))->validated();

        $this->assertSame('EUR', $data->currency);
    }

    /**
     * The default form has a $1 custom amount minimum and a $100 level. "0100" casts to the exempt level
     * but is parsed as money as $0.10, below the minimum.
     *
     * @since 4.19.0.2
     */
    public function testValidatedShouldRejectAmountThatParsesBelowTheMinimum(): void
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $request = array_merge($this->donationRequestData($form, give_get_currency($form->id)), ['amount' => '0100']);

        try {
            DonateFormRouteData::fromRequest($request)->validated();
            $this->fail('Expected a validation error for an amount that parses below the minimum.');
        } catch (DonationFormFieldErrorsException $exception) {
            $this->assertNotEmpty($exception->getError()->get_error_messages('amount'));
        }
    }

    /**
     * The final submission route fires givewp_donation_form_fields_validated as a final submission,
     * which is the single point where spam-protection listeners should run the Akismet check (all
     * donation data is available and the donation is about to be processed).
     *
     * @since 4.16.0
     */
    public function testValidatedFiresFieldsValidatedActionAsFinalSubmission()
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

        add_filter("givewp_donation_forms_honeypot_enabled", "__return_false");

        $isFinalSubmission = null;
        add_action(
            'givewp_donation_form_fields_validated',
            static function ($values, $isFinal = true) use (&$isFinalSubmission) {
                $isFinalSubmission = $isFinal;
            },
            10,
            2
        );

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
            'donationType' => $data->donationType->getValue(),
        ]);

        DonateFormRouteData::fromRequest($request)->validated();

        $this->assertTrue($isFinalSubmission);
    }

    /**
     * @since 3.17.0 updated to ignore honeypot field
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

        add_filter("givewp_donation_forms_honeypot_enabled", "__return_false");

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

    /**
     * @since 3.14.0
     * @dataProvider donationFormStatusProvider
     * @throws Exception
     */
    public function testValidatedShouldThrowExceptionDonationFormForbidden(DonationFormStatus $formStatus): void
    {
        $this->expectException(DonationFormForbidden::class);

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create(['status' => $formStatus]);

        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

        $form->save();

        $form = DonationForm::find($form->id);

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
            'donationType' => $data->donationType->getValue(),
        ]);

        $formData = DonateFormRouteData::fromRequest($request);

        $formData->validated();
    }

    /**
     * @since 3.14.0
     * @throws Exception
     */
    public function testValidatedShouldThrowExceptionDonationFormFieldErrorsException(): void
    {

        $this->expectException(DonationFormFieldErrorsException::class);

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
        $data->text_block_meta = null;
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
            'text_block_meta' => null,
            'donationType' => $data->donationType->getValue(),
        ]);

        $formData = DonateFormRouteData::fromRequest($request);

        $formData->validated();
    }

    /**
     * @since 4.17.0
     * @dataProvider originUrlProvider
     */
    public function testFromRequestValidatesOriginUrl($originUrl, $expected): void
    {
        $formData = DonateFormRouteData::fromRequest([
            'formId' => 1,
            'gatewayId' => TestGateway::id(),
            'originUrl' => $originUrl,
            'isEmbed' => true,
            'embedId' => 'test-embed',
        ]);

        $this->assertSame($expected ?? home_url(), $formData->originUrl);
    }

    /**
     * @since 4.17.0
     */
    public function originUrlProvider(): array
    {
        // A null expectation means the home URL fallback.
        return [
            'https url' => ['https://external-site.test/donate', 'https://external-site.test/donate'],
            'http url' => ['http://localhost:8080/donate', 'http://localhost:8080/donate'],
            'url with query and fragment' => ['https://example.org/give?x=1#form', 'https://example.org/give?x=1#form'],
            'empty string stays empty' => ['', ''],
            'array is treated as absent' => [['https://external-site.test'], ''],
            'zero string' => ['0', null],
            'javascript scheme' => ['javascript:alert(1)', null],
            'data scheme' => ['data:text/html,<script>alert(1)</script>', null],
            'scheme-relative url' => ['//evil.test/donate', null],
            'not a url' => ['not a url', null],
        ];
    }

    /**
     * @since 3.14.0
     */
    public function donationFormStatusProvider(): array
    {
        $notAllowed = [];
        $allowed = [DonationFormStatus::PUBLISHED()->getValue()];
        foreach (DonationFormStatus::values() as $status) {
          if (!in_array($status->getValue(), $allowed, true)) {
            $notAllowed[] = [$status];
          }
        }

        return $notAllowed;
    }

    /**
     * @since 4.19.0.1
     */
    private function donationRequestData(DonationForm $form, string $currency): array
    {
        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

        add_filter('givewp_donation_forms_honeypot_enabled', '__return_false');

        $data = new DonateControllerData();
        $data->gatewayId = TestGateway::id();
        $data->amount = 100;
        $data->currency = $currency;
        $data->firstName = "Bill";
        $data->lastName = "Murray";
        $data->email = "billmurray@givewp.com";
        $data->formId = $form->id;
        $data->formTitle = $form->title;
        $data->company = null;
        $data->wpUserId = 0;
        $data->honorific = null;
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

        return array_merge(get_object_vars($data), [
            'donationType' => $data->donationType->getValue(),
        ]);
    }
}
