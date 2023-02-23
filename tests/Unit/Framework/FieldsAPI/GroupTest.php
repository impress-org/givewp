<?php

namespace Give\Tests\Unit\Framework\FieldsAPI;

use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Text;
use PHPUnit\Framework\TestCase;

final class GroupTest extends TestCase
{

    public function testHasName()
    {
        $this->assertEquals('group', Group::make('group')->getName());
    }

    public function testGetNodeByName()
    {
        $group = Group::make('group')->append(
            Text::make('firstTextField'),
            Text::make('secondTextField')
        );

        $this->assertEquals('secondTextField', $group->getNodeByName('secondTextField')->getName());
    }

    public function testGetNestedNodeByName()
    {
        $group = Group::make('group')->append(
            Text::make('firstTextField'),
            Group::make('nestedGroup')->append(
                Text::make('secondTextField')
            )
        );

        $this->assertEquals('secondTextField', $group->getNodeByName('secondTextField')->getName());
    }

    /**
     * @since 2.25.0
     */
    public function testGetFieldsInNestedGroups()
    {
        $group = Group::make('group')->append(
            Text::make('firstTextField'),
            Text::make('secondTextField'),
            Group::make('nestedGroup')->append(
                Text::make('thirdTextField'),
                Group::make('nestedNestedGroup')->append(
                    Text::make('fourthTextField')
                )
            )
        );

        $fields = $group->getFields();
        $this->assertCount(4, $fields);

        $this->assertEquals([
            'firstTextField',
            'secondTextField',
            'thirdTextField',
            'fourthTextField',
        ],
            array_map(static function ($field) {
                return $field->getName();
            }, $fields));
    }
}
