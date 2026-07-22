<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\ConvertDonationAmountBlockToFieldsApi;
use Give\FormBuilder\BlockModels\DonationAmountBlockModel;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;

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
