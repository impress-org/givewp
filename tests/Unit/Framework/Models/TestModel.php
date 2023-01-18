<?php

namespace Give\Tests\Unit\Framework\Models;

use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Model;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Tests\TestCase;

/**
 * @since 2.20.1
 *
 * @coversDefaultClass Model
 */
class TestModel extends TestCase
{
    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testFillShouldAssignProperties()
    {
        $model = new MockModel();

        $model->fill(['id' => 1, 'firstName' => 'Bill', 'lastName' => 'Murray']);

        $this->assertEquals(1, $model->id);
        $this->assertEquals('Bill', $model->firstName);
        $this->assertEquals('Murray', $model->lastName);
    }

    /**
     * @since 2.20.0
     *
     * @return void
     */
    public function testDefaultPropertyValues()
    {
        $model = new MockModel();

        $this->assertNull($model->id);
        $this->assertSame('Michael', $model->firstName);
        $this->assertNull($model->lastName);
        $this->assertSame([], $model->emails);
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testConstructorShouldFillAttributesAndAssignProperties()
    {
        $model = new MockModel(['id' => 1, 'firstName' => 'Bill', 'lastName' => 'Murray']);

        $this->assertEquals(1, $model->id);
        $this->assertEquals('Bill', $model->firstName);
        $this->assertEquals('Murray', $model->lastName);
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testGetAttributeShouldReturnPropertyValue()
    {
        $model = new MockModel(['id' => 1]);

        $this->assertEquals(1, $model->getAttribute('id'));
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testGetAttributeShouldThrowInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $model = new MockModel(['id' => 1]);

        $model->getAttribute('iDontExist');
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testSetAttributeShouldAssignPropertyValue()
    {
        $model = new MockModel(['id' => 1]);
        $model->setAttribute('firstName', 'Bill');

        $this->assertEquals('Bill', $model->firstName);
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testIsPropertyTypeValidShouldReturnTrueWhenPropertyIsValid()
    {
        $model = new MockModel();

        $this->assertTrue($model->isPropertyTypeValid('id', 1));
    }

    /**
     * @since 2.20.1
     *
     * @dataProvider invalidTypeProvider
     *
     * @return void
     */
    public function testIsPropertyTypeValidShouldReturnFalseWhenPropertyIsInValid($key, $value)
    {
        $model = new MockModel();

        $this->assertFalse($model->isPropertyTypeValid($key, $value));
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testModelShouldHaveDirtyAttributes()
    {
        $model = new MockModel(
            [
                'id' => 1,
                'firstName' => 'Bill',
                'lastName' => 'Murray',
                'emails' => ['billMurray@givewp.com'],
            ]
        );

        $model->lastName = 'Gates';

        $this->assertEquals(['lastName' => 'Gates'], $model->getDirty());
        $this->assertEquals(true, $model->isDirty());
        $this->assertTrue($model->isDirty('lastName'));
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testModelShouldHaveCleanAttributes()
    {
        $model = new MockModel(
            [
                'id' => 1,
                'firstName' => 'Bill',
                'lastName' => 'Murray',
                'emails' => ['billMurray@givewp.com'],
            ]
        );

        $model->lastName = 'Gates';

        $this->assertEquals(false, $model->isClean());
        $this->assertEquals(true, $model->isClean('firstName'));
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testIssetShouldReturnTrue()
    {
        $model = new MockModel(['id' => 0]);

        $this->assertTrue(isset($model->id));
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testIssetShouldReturnFalse()
    {
        $model = new MockModel();

        $this->assertFalse(isset($model->id));

        $model->id = null;

        $this->assertFalse(isset($model->id));
    }

    /**
     * @since 2.20.1
     *
     * @dataProvider invalidTypeProvider
     *
     * @return void
     */
    public function testModelShouldThrowExceptionForAssigningInvalidPropertyType($key, $value)
    {
        $this->expectException(InvalidArgumentException::class);

        new MockModel([$key => $value]);
    }

    /**
     * @return void
     */
    public function testModelRelationshipPropertyShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $model = new MockModelWithRelationship();

        $model->relatedButNotCallable;
    }

    /**
     * @since 2.20.1
     *
     * @return void
     */
    public function testModelRelationshipPropertyShouldReturnCallable()
    {
        $model = new MockModelWithRelationship();

        $this->assertEquals($model->relatedAndCallableHasOne, $model->relatedAndCallableHasOne()->get());
        $this->assertEquals($model->relatedAndCallableHasMany, $model->relatedAndCallableHasMany()->getAll());
    }

    /**
     * @since 2.20.0
     */
    public function testModelRelationsShouldBeCached()
    {
        $model = new MockModelWithRelationship();

        $post = $model->relatedAndCallableHasOne;

        self::assertSame($model->relatedAndCallableHasOne, $post);
    }

    /**
     * @since 2.22.3
     */
    public function testShouldThrowExceptionForGettingMissingProperty()
    {
        $this->expectException(InvalidArgumentException::class);

        $model = new MockModel();

        $model->iDontExist;
    }

    /**
     * @since 2.22.3
     */
    public function testShouldThrowExceptionForSettingMissingProperty()
    {
        $this->expectException(InvalidArgumentException::class);

        $model = new MockModel();

        $model->iDontExist = 'foo';
    }

    /**
     * @since 2.20.1
     *
     * @return array
     */
    public function invalidTypeProvider()
    {
        return [
            ['id', 'Not an integer'],
            ['firstName', 100],
            ['emails', 'Not an array'],
        ];
    }
}

/**
 * @since 2.20.1
 *
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 */
class MockModel extends Model
{
    protected $properties = [
        'id' => 'int',
        'firstName' => ['string', 'Michael'],
        'lastName' => 'string',
        'emails' => ['array', []],
    ];
}

/**
 * @since 2.20.1
 *
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 * @property Model|null $relatedAndCallableHasOne
 * @property Model[]|null $relatedAndCallableHasMany
 */
class MockModelWithRelationship extends Model
{
    protected $properties = [
        'id' => 'int',
    ];

    protected $relationships = [
        'relatedButNotCallable' => Relationship::HAS_ONE,
        'relatedAndCallableHasOne' => Relationship::HAS_ONE,
        'relatedAndCallableHasMany' => Relationship::HAS_MANY,
    ];

    /**
     * @return QueryBuilder
     */
    public function relatedAndCallableHasOne()
    {
        return DB::table('posts');
    }

    /**
     * @return QueryBuilder
     */
    public function relatedAndCallableHasMany()
    {
        return DB::table('posts');
    }
}
