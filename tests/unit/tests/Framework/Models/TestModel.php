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
     * @coversDefaultClass
     *
     * @return void
     */
    public function testIsPropertyTypeValidShouldReturnFalseWhenPropertyIsInValid()
    {
        $model = new MockModel();

        $this->assertFalse($model->isPropertyTypeValid('id', 'Not an int'));
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testModelShouldHaveDirtyAttributes()
    {
        $model = new MockModel(['id' => 1, 'firstName' => 'Bill', 'lastName' => 'Murray']);

        $model->lastName = 'Gates';

        $this->assertEquals(['lastName' => 'Gates'], $model->getDirty());
        $this->assertTrue($model->isDirty('lastName'));
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testModelShouldThrowExceptionForAssigningInvalidPropertyType()
    {
        $this->expectException(InvalidArgumentException::class);

        new MockModel(['id' => 'Not an integer']);
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
        'lastName' => 'string'
    ];
}
