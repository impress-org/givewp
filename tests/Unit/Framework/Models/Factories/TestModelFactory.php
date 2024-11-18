<?php

namespace Give\Tests\Unit\Framework\Models\Factories;

use Exception;
use Give\Framework\Models\Factories\ModelFactory;
use Give\Framework\Models\Model;
use Give\Tests\TestCase;

/**
 * @unreleased
 */
class TestModelFactory extends TestCase
{

    /**
     * @unreleased
     */
    public function setUp()
    {
        parent::setUp();
        MockModel::resetAutoIncrementId();
    }

    /**
     * @unreleased
     */
    public function testDefinitionAsAttributes()
    {
        $factory = new class(MockModel::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'id' => 123,
                ];
            }
        };

        $object = $factory->make();

        $this->assertEquals(123, $object->id);
    }

    /**
     * @unreleased
     */
    public function testPassedAttributesOverrideDefinition()
    {
        $factory = new class(MockModel::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'id' => 123,
                ];
            }
        };

        $object = $factory->make([
            'id' => 456,
        ]);

        $this->assertEquals(456, $object->id);
    }

    /**
     * @unreleased
     */
    public function testResolvesCallableDefinitions()
    {
        $factory = new class(MockModel::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'id' => function() {
                        return 123;
                    },
                ];
            }
        };

        $object = $factory->make();

        $this->assertEquals(123, $object->id);
    }

    /**
     * @unreleased
     */
    public function testDoesNotResolveCallableWhenPassedAttribute()
    {
        $factory = new class(MockModel::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'id' => function() {
                        throw new Exception('Should not be called');
                    },
                ];
            }
        };

        $object = $factory->make([
            'id' => 123,
        ]);

        $this->assertEquals(123, $object->id);
    }

    /**
     * @unreleased
     */
    public function testMakeResolvesDependencyDefinition()
    {
        $factory = new class(MockModelWithDependency::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'id' => 123,
                ];
            }
        };

        $nestedFactory = new class(MockModel::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'id' => 789,
                ];
            }
        };

        $object = $factory->make([
            'nestedId' => $nestedFactory->makeAndResolveTo('id'),
        ]);

        $this->assertEquals(789, $object->nestedId);
    }

    /**
     * @unreleased
     */
    public function testMakeResolvesDependencyDefinitionWithCount()
    {
        $factory = new class(MockModelWithDependency::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'id' => 123,
                ];
            }
        };

        $nestedFactory = new class(MockModel::class) extends ModelFactory {
            public function definition(): array {
                static $counter = 1;
                return [
                    'id' => $counter++,
                ];
            }
        };

        $instances = $factory->count(2)->make([
            'nestedId' => $nestedFactory->makeAndResolveTo('id'),
        ]);

        $this->assertIsArray($instances);
        $this->assertEquals(1, $instances[0]->nestedId);
        $this->assertEquals(2, $instances[1]->nestedId);
    }

    /**
     * @unreleased
     */
    public function testMakeDoesNotResolveDependencyWhenPassedAttribute()
    {
        $factory = new class(MockModelWithDependency::class) extends ModelFactory {
            public function definition(): array {

                $nestedFactory = new class(MockModel::class) extends ModelFactory {
                    public function definition(): array {
                        return [
                            'id' => 456,
                        ];
                    }

                    public function make(array $attributes = [])
                    {
                        throw new Exception('Should not be called');
                    }
                };

                return [
                    'id' => 123,
                    'nestedId' => $nestedFactory->makeAndResolveTo('id')
                ];
            }
        };

        $object = $factory->make([
            'nestedId' => 789,
        ]);

        $this->assertEquals(789, $object->nestedId);
    }

    /**
     * @unreleased
     */
    public function testCreateResolvesDependencyDefinition()
    {
        $factory = new class(MockModelWithDependency::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'id' => 123,
                ];
            }
        };

        $nestedFactory = new class(MockModel::class) extends ModelFactory {
            public function definition(): array {
                return [
                    // Defer id assignment until save
                ];
            }
        };

        $object = $factory->create([
            'nestedId' => $nestedFactory->createAndResolveTo('id'),
        ]);

        $this->assertEquals(1, $object->nestedId);
    }

    /**
     * @unreleased
     */
    public function testCreateResolvesDependencyDefinitionWithCount()
    {
        $factory = new class(MockModelWithDependency::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'id' => 123,
                ];
            }
        };

        $nestedFactory = new class(MockModel::class) extends ModelFactory {
            public function definition(): array {
                return [
                    // Defer id assignment until save
                ];
            }
        };

        $instances = $factory->count(2)->make([
            'nestedId' => $nestedFactory->createAndResolveTo('id'),
        ]);

        $this->assertIsArray($instances);
        $this->assertEquals(1, $instances[0]->nestedId);
        $this->assertEquals(2, $instances[1]->nestedId);
    }

    /**
     * @unreleased
     */
    public function testCreateDoesNotResolveDependencyWhenPassedAttribute()
    {
        $factory = new class(MockModelWithDependency::class) extends ModelFactory {
            public function definition(): array {

                $nestedFactory = new class(MockModel::class) extends ModelFactory {
                    public function definition(): array {
                        return [
                            // Defer id assignment until save
                        ];
                    }

                    public function create(array $attributes = [])
                    {
                        throw new Exception('Should not be called');
                    }
                };

                return [
                    'id' => 123,
                    'nestedId' => $nestedFactory->createAndResolveTo('id')
                ];
            }
        };

        $object = $factory->make([
            'nestedId' => 789,
        ]);

        $this->assertEquals(789, $object->nestedId);
    }

    public function testDoesNotResolveInvokableClasses()
    {
        $factory = new class(MockModelWithInvokableProperty::class) extends ModelFactory {
            public function definition(): array {
                return [
                    'invokable' => new class extends MockInvokableClass {
                        public function __invoke() {
                            throw new \Exception('Invokable classes should not be resolved by factories.');
                        }
                    },
                ];
            }
        };

        $object = $factory->make();

        $this->assertInstanceOf(MockInvokableClass::class, $object->invokable);
    }
}

/**
 * @unreleased
 *
 * @property int $id
 */
class MockModel extends Model
{
    protected static $autoIncrementId = 1;

    protected $properties = [
        'id' => 'int',
    ];

    public function save() {
        $this->id = $this->id ?: self::$autoIncrementId++;
        return $this;
    }

    public static function resetAutoIncrementId() {
        self::$autoIncrementId = 1;
    }
}

/**
 * @unreleased
 *
 * @property int $id
 * @property int $nestedId
 */
class MockModelWithDependency extends MockModel
{
    protected $properties = [
        'id' => 'int',
        'nestedId' => 'int',
    ];
}

abstract class MockInvokableClass
{
    abstract public function __invoke();
}

class MockModelWithInvokableProperty extends MockModel
{
    protected $properties = [
        'invokable' => MockInvokableClass::class,
    ];
}
