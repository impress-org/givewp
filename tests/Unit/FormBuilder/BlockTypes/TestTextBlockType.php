<?php

namespace Give\Tests\Unit\FormBuilder\BlockTypes;

use Exception;
use Give\Framework\Blocks\BlockModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestTextBlockType extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    public function testNameShouldMatch(): void
    {
        $blockModel = BlockModel::make(
            [
                'name' => 'givewp/text',
                'attributes' => [
                    'label' => 'Test Label',
                    'description' => 'Test Description',
                ]
            ]
        );

        $blockType = new \Give\FormBuilder\BlockTypes\TextBlockType($blockModel);

        $this->assertSame('givewp/text', $blockType::name());
    }

    /**
     * @since 3.8.0
     *
     * @throws Exception
     */
    public function testAttributesShouldMatchProperties(): void
    {
        $blockModel = BlockModel::make(
            [
                'name' => 'givewp/text',
                'attributes' => [
                    'label' => 'Test Label',
                    'description' => 'Test Description',
                    'placeholder' => 'Test Placeholder',
                    'isRequired' => true,
                    'displayInAdmin' => true,
                    'displayInReceipt' => true,
                    'defaultValue' => 'Test Default Value',
                    'emailTag' => 'test@givewp.com',
                    'fieldName' => 'test_field_name',
                    'conditionalLogic' => [
                        'enabled' => true,
                        'action' => 'show',
                        'boolean' => 'and',
                        'rules' => [
                            [
                                'field' => 'Test Field ID',
                                'operator' => '=',
                                'value' => '100'
                            ]
                        ]
                    ],
                ]
            ]
        );

        $blockType = new \Give\FormBuilder\BlockTypes\TextBlockType($blockModel);

        $this->assertSame('Test Label', $blockType->label);
        $this->assertSame('Test Description', $blockType->description);
        $this->assertSame('Test Placeholder', $blockType->placeholder);
        $this->assertTrue($blockType->isRequired);
        $this->assertTrue($blockType->displayInAdmin);
        $this->assertTrue($blockType->displayInReceipt);
        $this->assertSame('Test Default Value', $blockType->defaultValue);
        $this->assertSame('test@givewp.com', $blockType->emailTag);
        $this->assertSame('test_field_name', $blockType->fieldName);
        $this->assertSame(
            [
                'enabled' => true,
                'action' => 'show',
                'boolean' => 'and',
                'rules' => [
                    [
                        'field' => 'Test Field ID',
                        'operator' => '=',
                        'value' => '100'
                    ]
                ]
            ],
            $blockType->conditionalLogic
        );
    }
}
