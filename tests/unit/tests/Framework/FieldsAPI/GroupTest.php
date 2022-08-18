<?php

use Give\Framework\FieldsAPI\Concerns\TapNode;
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

    public function testNestedNodeCanBeTappedAndOriginalGroupIsReturned()
    {
        $group = Group::make('group')->append(
            $node = new class extends Text {

                public $updated = false;

                public function __construct()
                {
                    $this->name = 'myField';
                }
            }
        );

        $this->assertEquals($group, $group->tapNode('myField', function ($tappedNode) {
            $tappedNode->updated = true;
        }));

        $this->assertTrue($node->updated);
    }
}
