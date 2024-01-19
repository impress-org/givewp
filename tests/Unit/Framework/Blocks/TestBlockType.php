<?php

namespace Give\Tests\Unit\Framework\Blocks;

use Exception;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\Blocks\BlockType;
use Give\Tests\TestCase;
use RuntimeException;

/**
 * @unreleased
 */
class TestBlockType extends TestCase
{
    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldThrowExceptionWhenBlockTypeAndBlockModelNamesDoNotMatch(): void
    {
        $this->expectException(RuntimeException::class);

        $blockModel = new BlockModel('givewp/not-donation-amount');

        new class ($blockModel) extends BlockType {
            public function getName(): string
            {
                return 'givewp/donation-amount';
            }
        };
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testBlockModelAndBlockTypeShouldHaveMatchingNames(): void
    {
        $blockModel = new BlockModel('givewp/donation-amount');

        $blockType = new class ($blockModel) extends BlockType {
            public function getName(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertEquals('givewp/donation-amount', $blockType->getName());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldHavePropertiesThatMatchBlockModelAttributes(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'label' => 'Donation Amount',
                'recurringEnabled' => false,
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'label' => 'string',
                'recurringEnabled' => 'bool',
            ];

            public function getName(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertEquals('Donation Amount', $blockType->label);
        $this->assertEquals(false, $blockType->recurringEnabled);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldCastBoolPropertyValues(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'falsyAttribute' => false,
                'falsyStringAttribute' => 'false',
                'falsyIntAttribute' => 0,
                'truthyAttribute' => true,
                'truthyStringAttribute' => 'true',
                'truthyIntAttribute' => 1,
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'falsyAttribute' => 'bool',
                'falsyStringAttribute' => 'bool',
                'falsyIntAttribute' => 'bool',
                'truthyAttribute' => 'bool',
                'truthyStringAttribute' => 'bool',
                'truthyIntAttribute' => 'bool',
            ];

            public function getName(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertFalse($blockType->falsyAttribute);
        $this->assertFalse($blockType->falsyStringAttribute);
        $this->assertFalse($blockType->falsyIntAttribute);
        $this->assertTrue($blockType->truthyAttribute);
        $this->assertTrue($blockType->truthyStringAttribute);
        $this->assertTrue($blockType->truthyIntAttribute);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldCastStringPropertyValues(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'stringAttribute' => '123',
                'intStringAttribute' => 123,
                'floatStringAttribute' => 123.00,
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'stringAttribute' => 'string',
                'intStringAttribute' => 'string',
                'floatStringAttribute' => 'string',
            ];

            public function getName(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertEquals('123', $blockType->stringAttribute);
        $this->assertEquals('123', $blockType->intStringAttribute);
        $this->assertEquals('123', $blockType->floatStringAttribute);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldCastIntPropertyValues(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'intAttribute' => 123,
                'stringIntAttribute' => '123',
                'floatIntAttribute' => 123.00,
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'intAttribute' => 'int',
                'stringIntAttribute' => 'int',
                'floatIntAttribute' => 'int',
            ];

            public function getName(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertEquals(123, $blockType->intAttribute);
        $this->assertEquals(123, $blockType->stringIntAttribute);
        $this->assertEquals(123, $blockType->floatIntAttribute);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldCastArrayPropertyValues(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'arrayAttribute' => ['green', 'eggs', 'and', 'ham'],
                'intArrayAttribute' => 123,
                'stringArrayAttribute' => 'green'
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'arrayAttribute' => 'array',
                'intArrayAttribute' => 'array',
                'stringArrayAttribute' => 'array',
            ];

            public function getName(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertEquals(['green', 'eggs', 'and', 'ham'], $blockType->arrayAttribute);
        $this->assertEquals([123], $blockType->intArrayAttribute);
        $this->assertEquals(['green'], $blockType->stringArrayAttribute);
    }
}
