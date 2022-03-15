<?php

namespace unit\tests\Framework\Models;

use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Model;
use Give\Framework\Models\ValueObjects\Relationship;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @unreleased
 *
 * @coversDefaultClass Model
 */
class TestModel extends \Give_Unit_Test_Case
{

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     *
     * @return void
     */
    public function testGetAttributeShouldReturnPropertyValue()
    {
        $model = new MockModel(['id' => 1]);

        $this->assertEquals(1, $model->getAttribute('id'));
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     *
     * @return void
     */
    public function testIsPropertyTypeValidShouldReturnTrueWhenPropertyIsValid()
    {
        $model = new MockModel();

        $this->assertTrue($model->isPropertyTypeValid('id', 1));
    }

    /**
     * @unreleased
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
     * @unreleased
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
                'emails' => ['billMurray@givewp.com']
            ]
        );

        $model->lastName = 'Gates';

        $this->assertEquals(['lastName' => 'Gates'], $model->getDirty());
        $this->assertTrue($model->isDirty('lastName'));
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testIssetShouldReturnTrue()
    {
        $model = new MockModel(['id' => 0]);

        $this->assertTrue(isset($model->id));
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     *
     * @return void
     */
    public function testModelRelationshipPropertyShouldReturnCallable()
    {
        $model = new MockModelWithRelationship();

        $this->assertEquals($model->relatedAndCallableOneToOne, $model->relatedAndCallableOneToOne()->get());
        $this->assertEquals($model->relatedAndCallableOneToMany, $model->relatedAndCallableOneToMany()->getAll());
    }

    /**
     * @unreleased
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
 * @unreleased
 *
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 */
class MockModel extends Model
{
    protected $properties = [
        'id' => 'int',
        'firstName' => 'string',
        'lastName' => 'string',
        'emails' => 'array'
    ];
}


/**
 * @unreleased
 *
 * @property int $id
 * @property string $firstName
 * @property string $lastName
 * @property Model|null $relatedAndCallableOneToOne
 * @property Model[]|null $relatedAndCallableOneToMany
 */
class MockModelWithRelationship extends Model
{
    protected $properties = [
        'id' => 'int',
    ];

    protected $relationships = [
        'relatedButNotCallable' => Relationship::ONE_TO_ONE,
        'relatedAndCallableOneToOne' => Relationship::ONE_TO_ONE,
        'relatedAndCallableOneToMany' => Relationship::ONE_TO_MANY,
    ];

    /**
     * @return QueryBuilder
     */
    public function relatedAndCallableOneToOne()
    {
        return DB::table('posts');
    }

    /**
     * @return QueryBuilder
     */
    public function relatedAndCallableOneToMany()
    {
        return DB::table('posts');
    }
}
