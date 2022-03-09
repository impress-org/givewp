<?php

namespace unit\tests\Framework\Models;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Model;

/**
 * @unreleased
 *
 * @coversDefaultClass Model
 */
class TestModel extends \Give_Unit_Test_Case {

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
    public function testSetAttributeShouldAssignPropertyValue()
    {
        $model = new MockModel(['id' => 1]);
        $model->setAttribute('firstName', 'Bill');

        $this->assertEquals('Bill', $model->firstName);
    }

    /**
     * @unreleased
     *
     * @coversDefaultClass
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
class MockModel extends Model {
    protected $properties = [
        'id' => 'int',
        'firstName' => 'string',
        'lastName' => 'string',
        'emails' => 'array'
    ];
}
