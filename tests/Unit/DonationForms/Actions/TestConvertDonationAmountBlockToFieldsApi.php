<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\ConvertDonationAmountBlockToFieldsApi;
use Give\DonationForms\Rules\Max;
use Give\DonationForms\Rules\Min;
use Give\FormBuilder\BlockModels\DonationAmountBlockModel;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\FieldsAPI\Actions\CreateValidatorFromForm;
use Give\Framework\FieldsAPI\Amount;
use Give\Framework\FieldsAPI\DonationAmount;
use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Section;
use Give\Tests\TestCase;
use Give\Vendors\StellarWP\Validation\Validator;

/**
 * @since 4.16.5
 * @covers Give\DonationForms\Actions\ConvertDonationAmountBlockToFieldsApi
 */
final class TestConvertDonationAmountBlockToFieldsApi extends TestCase
{
    /**
     * @since 4.16.5
     */
    public function testReturnsCheckedLevelValueAsTopLevelChecked(): void
    {
        $result = $this->_prepareLevelsArray(
            [
                [
                    'id'      => 1,
                    'value'   => 10,
                    'checked' => true,
                ],
                [
                    'id'      => 1,
                    'value'   => 20,
                    'checked' => false,
                ],
            ]
        );

        $this->assertSame($result['checked'], 10.0);
    }

    /**
     * Test that when no checked option exists, the checked value returned 
     * is null.
     *
     * @since 4.16.5
     */
    public function testReturnsCheckedNullWhenNoCheckedOptionExists(): void
    {
        $result = $this->_prepareLevelsArray(
            [
                [
                    'id'      => 1,
                    'value'   => 10,
                    'checked' => false,
                ],
                [
                    'id'      => 1,
                    'value'   => 20,
                    'checked' => false,
                ],
            ]
        );

        $this->assertSame($result['checked'], null);
    }

    /**
     * Test that the checked value exists in the level array.
     *
     * @since 4.16.5
     */
    public function testCheckedValueExistsInTheLevelArray(): void
    {
     
        $result = $this->_prepareLevelsArray(
            [
                [
                    'id'      => 1,
                    'value'   => 10,
                    'checked' => false,
                ],
                [
                    'id'      => 1,
                    'value'   => 20,
                    'checked' => false,
                ],
            ]
        );

        foreach ( $result['levels'] as $level ) {
            $this->assertArrayHasKey('checked', $level);
        }
    }

    /**
     * Test that the label is included when description is enabled.
     *
     * @since 4.16.5
     */
    public function testIncludesLabelWhenDescriptionIsEnabled(): void {
        $result = $this->_prepareLevelsArray(
            [
                [
                    'id'      => 1,
                    'value'   => 10,
                    'label'   => 'Ten',
                    'checked' => false,
                ],
                [
                    'id'      => 1,
                    'value'   => 20,
                    'label'   => 'Twenty',
                    'checked' => false,
                ],
            ],
            true
        );

        foreach ( $result['levels'] as $level ) {
            $this->assertNotSame($level['label'], '');
        }
    }

    /**
     * Test that the label is omitted when description is disabled.
     *
     * @since 4.16.5
     */
    public function testOmitsLabelWhenDescriptionIsDisabled(): void {
        $result = $this->_prepareLevelsArray(
            [
                [
                    'id'      => 1,
                    'value'   => 10,
                    'label'   => 'Ten',
                    'checked' => false,
                ],
                [
                    'id'      => 1,
                    'value'   => 20,
                    'label'   => 'Twenty',
                    'checked' => false,
                ],
            ],
            false
        );

        foreach ( $result['levels'] as $level ) {
            $this->assertSame($level['label'], '');
        }
    }


    /**
     * @since TBD
     */
    public function testValidatesADonationLevelBelowTheCustomAmountMinimum(): void
    {
        $validator = $this->_validator(
            [
                'priceOption' => 'multi',
                'levels' => [['value' => 10], ['value' => 25], ['value' => 500, 'checked' => true]],
                'customAmount' => true,
                'customAmountMin' => 500,
            ],
            10
        );

        $this->assertTrue($validator->passes(), print_r($validator->errors(), true));
    }

    /**
     * @since TBD
     */
    public function testRejectsACustomAmountBelowTheCustomAmountMinimum(): void
    {
        $validator = $this->_validator(
            [
                'priceOption' => 'multi',
                'levels' => [['value' => 10], ['value' => 25], ['value' => 500, 'checked' => true]],
                'customAmount' => true,
                'customAmountMin' => 500,
            ],
            11
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('greater than or equal to 500', $validator->errors()['amount']);
    }

    /**
     * @since TBD
     */
    public function testValidatesADonationLevelAboveTheCustomAmountMaximum(): void
    {
        $validator = $this->_validator(
            [
                'priceOption' => 'multi',
                'levels' => [['value' => 10, 'checked' => true], ['value' => 250]],
                'customAmount' => true,
                'customAmountMin' => 1,
                'customAmountMax' => 100,
            ],
            250
        );

        $this->assertTrue($validator->passes(), print_r($validator->errors(), true));
    }

    /**
     * @since TBD
     */
    public function testRejectsACustomAmountAboveTheCustomAmountMaximum(): void
    {
        $validator = $this->_validator(
            [
                'priceOption' => 'multi',
                'levels' => [['value' => 10, 'checked' => true], ['value' => 250]],
                'customAmount' => true,
                'customAmountMin' => 1,
                'customAmountMax' => 100,
            ],
            251
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('less than or equal to 100', $validator->errors()['amount']);
    }

    /**
     * @since TBD
     */
    public function testValidatesTheSetPriceBelowTheCustomAmountMinimum(): void
    {
        $validator = $this->_validator(
            [
                'priceOption' => 'set',
                'setPrice' => 25,
                'levels' => [],
                'customAmount' => true,
                'customAmountMin' => 500,
            ],
            25
        );

        $this->assertTrue($validator->passes(), print_r($validator->errors(), true));
    }

    /**
     * @since TBD
     */
    public function testExemptsDonationLevelsFromTheCustomAmountRange(): void
    {
        $amountNode = $this->_amountNode(
            [
                'priceOption' => 'multi',
                'levels' => [
                    ['value' => 10],
                    ['value' => 25],
                    ['value' => 500, 'checked' => true],
                ],
                'customAmount' => true,
                'customAmountMin' => 500,
                'customAmountMax' => 1000,
            ]
        );

        /** @var Min $min */
        $min = $amountNode->getValidationRules()->getRule('min');
        /** @var Max $max */
        $max = $amountNode->getValidationRules()->getRule('max');

        $this->assertSame([10.0, 25.0, 500.0], $min->getExemptAmounts());
        $this->assertSame([10.0, 25.0, 500.0], $max->getExemptAmounts());
    }

    /**
     * @since TBD
     */
    public function testExemptsTheSetPriceFromTheCustomAmountRange(): void
    {
        $amountNode = $this->_amountNode(
            [
                'priceOption' => 'set',
                'setPrice' => 25,
                'levels' => [],
                'customAmount' => true,
                'customAmountMin' => 500,
            ]
        );

        /** @var Min $min */
        $min = $amountNode->getValidationRules()->getRule('min');

        $this->assertSame([25.0], $min->getExemptAmounts());
    }

    /**
     * @since TBD
     */
    public function testLevelsWithoutAPositiveValueAreNotExempt(): void
    {
        $amountNode = $this->_amountNode(
            [
                'priceOption' => 'multi',
                'levels' => [
                    ['value' => 10],
                    ['value' => ''],
                    ['value' => 500, 'checked' => true],
                ],
                'customAmount' => true,
                'customAmountMin' => 500,
            ]
        );

        /** @var Min $min */
        $min = $amountNode->getValidationRules()->getRule('min');

        $this->assertSame([10.0, 500.0], $min->getExemptAmounts());
    }

    /**
     * @since TBD
     */
    public function testFallsBackToTheLowestDonationLevelWhenNoCustomAmountMinimumIsSet(): void
    {
        $attributes = [
            'priceOption' => 'multi',
            'levels' => [['value' => 25], ['value' => 10, 'checked' => true], ['value' => 500]],
            'customAmount' => true,
        ];

        /** @var Min $min */
        $min = $this->_amountNode($attributes)->getValidationRules()->getRule('min');

        $this->assertSame(10, $min->getSize());
        $this->assertTrue($this->_validator($attributes, 5)->fails());
        $this->assertTrue($this->_validator($attributes, 10)->passes());
        $this->assertTrue($this->_validator($attributes, 15)->passes());
    }

    /**
     * @since TBD
     */
    public function testFallsBackToTheLowestDonationLevelWhenTheCustomAmountMinimumIsZero(): void
    {
        $amountNode = $this->_amountNode(
            [
                'priceOption' => 'multi',
                'levels' => [['value' => 10, 'checked' => true], ['value' => 25]],
                'customAmount' => true,
                'customAmountMin' => 0,
            ]
        );

        /** @var Min $min */
        $min = $amountNode->getValidationRules()->getRule('min');

        $this->assertSame(10, $min->getSize());
    }

    /**
     * @since TBD
     */
    public function testFallsBackToTheSetPriceWhenNoCustomAmountMinimumIsSet(): void
    {
        $attributes = [
            'priceOption' => 'set',
            'setPrice' => 25,
            'levels' => [],
            'customAmount' => true,
        ];

        /** @var Min $min */
        $min = $this->_amountNode($attributes)->getValidationRules()->getRule('min');

        $this->assertSame(25, $min->getSize());
        $this->assertTrue($this->_validator($attributes, 5)->fails());
        $this->assertTrue($this->_validator($attributes, 25)->passes());
    }

    /**
     * @since TBD
     */
    public function testFallsBackToTheLowestDonationLevelWhenCustomAmountIsDisabled(): void
    {
        $attributes = [
            'priceOption' => 'multi',
            'levels' => [['value' => 25], ['value' => 10, 'checked' => true]],
            'customAmount' => false,
        ];

        /** @var Min $min */
        $min = $this->_amountNode($attributes)->getValidationRules()->getRule('min');

        $this->assertSame(10, $min->getSize());
        $this->assertTrue($this->_validator($attributes, 5)->fails());
        $this->assertTrue($this->_validator($attributes, 10)->passes());
    }

    /**
     * @since TBD
     */
    public function testIgnoresTheCustomAmountMinimumWhenCustomAmountIsDisabled(): void
    {
        $amountNode = $this->_amountNode(
            [
                'priceOption' => 'multi',
                'levels' => [['value' => 10, 'checked' => true], ['value' => 25]],
                'customAmount' => false,
                'customAmountMin' => 500,
            ]
        );

        /** @var Min $min */
        $min = $amountNode->getValidationRules()->getRule('min');

        $this->assertSame(10, $min->getSize());
    }

    /**
     * @since TBD
     */
    public function testAppliesNoMinimumWhenTheBlockDefinesNoAmounts(): void
    {
        $amountNode = $this->_amountNode(
            [
                'priceOption' => 'multi',
                'levels' => [],
                'customAmount' => true,
            ]
        );

        $this->assertFalse($amountNode->getValidationRules()->hasRule('min'));
    }

    /**
     * @since TBD
     */
    public function testKeepsAFractionalCustomAmountMinimum(): void
    {
        $attributes = [
            'priceOption' => 'multi',
            'levels' => [['value' => 5, 'checked' => true], ['value' => 50]],
            'customAmount' => true,
            'customAmountMin' => '10.50',
        ];

        /** @var Min $min */
        $min = $this->_amountNode($attributes)->getValidationRules()->getRule('min');

        $this->assertSame(10.5, $min->getSize());
        $this->assertTrue($this->_validator($attributes, 10.25)->fails());
        $this->assertTrue($this->_validator($attributes, 10.5)->passes());
    }

    /**
     * @since TBD
     */
    public function testKeepsAFractionalCustomAmountMaximum(): void
    {
        $attributes = [
            'priceOption' => 'multi',
            'levels' => [['value' => 5, 'checked' => true]],
            'customAmount' => true,
            'customAmountMax' => '10.50',
        ];

        /** @var Max $max */
        $max = $this->_amountNode($attributes)->getValidationRules()->getRule('max');

        $this->assertSame(10.5, $max->getSize());
        $this->assertTrue($this->_validator($attributes, 10.5)->passes());
        $this->assertTrue($this->_validator($attributes, 10.75)->fails());
    }

    /**
     * @since TBD
     */
    public function testKeepsAFractionalLowestDonationLevelAsTheFallbackMinimum(): void
    {
        $attributes = [
            'priceOption' => 'multi',
            'levels' => [['value' => '10.50', 'checked' => true], ['value' => 25]],
            'customAmount' => true,
        ];

        /** @var Min $min */
        $min = $this->_amountNode($attributes)->getValidationRules()->getRule('min');

        $this->assertSame(10.5, $min->getSize());
        $this->assertTrue($this->_validator($attributes, 10.25)->fails());
        $this->assertTrue($this->_validator($attributes, 10.5)->passes());
    }

    /**
     * Validates the given amount against the donation amount group built from the given block attributes.
     *
     * @since TBD
     */
    private function _validator(array $attributes, float $amount): Validator
    {
        $form = new Form('Test Form');
        $form->append(Section::make('Test Section')->append($this->_donationAmountGroup($attributes)));

        return (new CreateValidatorFromForm())(
            $form,
            [
                'amount' => $amount,
                'currency' => 'USD',
                'levelId' => 'custom',
                'donationType' => 'single',
            ]
        );
    }

    /**
     * Builds the donation amount group from the given block attributes and returns its amount field.
     *
     * @since TBD
     */
    private function _amountNode(array $attributes): Amount
    {
        /** @var Amount $amountNode */
        $amountNode = $this->_donationAmountGroup($attributes)->getNodeByName('amount');

        return $amountNode;
    }

    /**
     * @since TBD
     */
    private function _donationAmountGroup(array $attributes): DonationAmount
    {
        $block = new DonationAmountBlockModel(
            BlockModel::make(
                [
                    'name' => 'givewp/donation-amount',
                    'attributes' => array_merge(
                        [
                            'label' => 'Donation Amount',
                            'descriptionsEnabled' => false,
                        ],
                        $attributes
                    ),
                ]
            )
        );

        return (new ConvertDonationAmountBlockToFieldsApi())($block, 'USD');
    }

    /**
     * Invokes the private prepareLevelsArray() method against a donation amount block
     * built from the given levels.
     *
     * @since 4.16.5
     */
    private function _prepareLevelsArray(array $levels, bool $descriptionEnabled = false): array
    {
        $blockModel = new DonationAmountBlockModel(
            BlockModel::make(
                [
                    'name'       => 'givewp/donation-amount',
                    'attributes' => [
                        'levels'             => $levels,
                        'descriptionsEnabled' => $descriptionEnabled
                    ]
                ]
            )
        );

        $action = new ConvertDonationAmountBlockToFieldsApi();
        $method = (new \ReflectionClass($action))->getMethod('prepareLevelsArray');
        $method->setAccessible(true);
        return $method->invoke($action, $blockModel);
    }
}
