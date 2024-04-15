<?php

namespace Give\Tests\Unit\Framework\Blocks;

use Exception;
use Give\Framework\Blocks\BlockModel;
use Give\Framework\Blocks\BlockType;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
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
            public static function name(): string
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
            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertSame('givewp/donation-amount', $blockType::name());
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

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertSame('Donation Amount', $blockType->label);
        $this->assertSame(false, $blockType->recurringEnabled);
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

            public static function name(): string
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

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertSame('123', $blockType->stringAttribute);
        $this->assertSame('123', $blockType->intStringAttribute);
        $this->assertSame('123', $blockType->floatStringAttribute);
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

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertSame(123, $blockType->intAttribute);
        $this->assertSame(123, $blockType->stringIntAttribute);
        $this->assertSame(123, $blockType->floatIntAttribute);
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

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertSame(['green', 'eggs', 'and', 'ham'], $blockType->arrayAttribute);
        $this->assertSame([123], $blockType->intArrayAttribute);
        $this->assertSame(['green'], $blockType->stringArrayAttribute);
    }

    /**
     * @unreleased
     */
    public function testShouldCastFloatPropertyValues(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'floatAttribute' => 123.00,
                'stringFloatAttribute' => '123.00',
                'intFloatAttribute' => 123,
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'floatAttribute' => 'float',
                'stringFloatAttribute' => 'float',
                'intFloatAttribute' => 'float',
            ];

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertSame(123.00, $blockType->floatAttribute);
        $this->assertIsFloat($blockType->floatAttribute);
        $this->assertSame(123.00, $blockType->stringFloatAttribute);
        $this->assertIsFloat($blockType->stringFloatAttribute);
        $this->assertSame(123.00, $blockType->intFloatAttribute);
        $this->assertIsFloat($blockType->intFloatAttribute);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldThrowExceptionWhenSettingInvalidPropertyType(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'arrayAttribute' => ['green', 'eggs', 'and', 'ham'],
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'arrayAttribute' => 'array',
            ];

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $blockType->arrayAttribute = 'not an array';
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testShouldUpdateExistingPropertyValue(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'arrayAttribute' => ['green', 'eggs', 'and', 'ham'],
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'arrayAttribute' => 'array',
            ];

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $blockType->arrayAttribute = array_merge($blockType->arrayAttribute,['sam', 'i', 'am']);

        $this->assertSame(['green', 'eggs', 'and', 'ham', 'sam', 'i', 'am'], $blockType->arrayAttribute);
    }

    /**
     * @unreleased
     */
    public function testShouldSetNewProperty(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => []
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'arrayAttribute' => 'array',
            ];

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $blockType->arrayAttribute = ['green', 'eggs', 'and', 'ham', 'sam', 'i', 'am'];

        $this->assertSame(['green', 'eggs', 'and', 'ham', 'sam', 'i', 'am'], $blockType->arrayAttribute);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testIssetProperty(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'arrayAttribute' => ['green', 'eggs', 'and', 'ham'],
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'arrayAttribute' => 'array',
            ];

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertTrue(isset($blockType->arrayAttribute));
        $this->assertFalse(isset($blockType->notAnAttribute));
    }

    /**
     * @unreleased
     */
    public function testBlockTypeToArray(): void
    {
        $blockModel = BlockModel::make([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'arrayAttribute' => ['green', 'eggs', 'and', 'ham'],
                'stringAttribute' => 'hello',
                'intAttribute' => 123,
                'boolAttribute' => true,
            ]
        ]);

        $blockType = new class ($blockModel) extends BlockType {
            protected $properties = [
                'arrayAttribute' => 'array',
                'stringAttribute' => 'string',
                'intAttribute' => 'int',
                'boolAttribute' => 'bool',
            ];

            public static function name(): string
            {
                return 'givewp/donation-amount';
            }
        };

        $this->assertSame([
            'name' => 'givewp/donation-amount',
            'attributes' => [
                'arrayAttribute' => ['green', 'eggs', 'and', 'ham'],
                'stringAttribute' => 'hello',
                'intAttribute' => 123,
                'boolAttribute' => true,
            ]
        ], $blockType->toArray());
    }
}
