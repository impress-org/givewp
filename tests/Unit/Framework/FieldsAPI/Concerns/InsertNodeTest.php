<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Exceptions\ReferenceNodeNotFoundException;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Text;
use PHPUnit\Framework\TestCase;

final class InsertNodeTest extends TestCase
{

    public function testInsertAfter()
    {
        $group = Group::make('group')
	        ->append( Text::make( 'firstTextField' ) )
	        ->insertAfter( 'firstTextField', Text::make( 'secondTextField' ) );

        $this->assertEquals( 1, $group->getNodeIndexByName( 'secondTextField' ) );
    }

    public function testInsertAfterReferenceNotFound() {
	    $this->expectException( ReferenceNodeNotFoundException::class );

	    Group::make( 'group' )
			->insertAfter( 'nonExistentField', Text::make( 'newTextField' ) );
    }

    public function testNestedInsertAfter() {
        $group = Group::make( 'group' )
	        ->append(
	        	Text::make( 'topLevelTextField' ),
		        Group::make( 'nestedGroup' )
		            ->append(
		            	Text::make( 'nestedTextField' )
		            )
	        )
			->insertAfter( 'nestedTextField', Text::make( 'anotherNestedTextField' ) );

        $this->assertEquals( 1, $group->getNodeByName( 'nestedGroup' )->getNodeIndexByName( 'anotherNestedTextField' ) );
    }

    public function testInsertBefore() {
        $group = Group::make( 'group' )
	        ->append( Text::make( 'firstTextField' ) )
	        ->insertBefore( 'firstTextField', Text::make( 'secondTextField' ) );

        $this->assertEquals( 0, $group->getNodeIndexByName( 'secondTextField' ) );
    }

    public function testInsertBeforeReferenceNotFound() {
	    $this->expectException( ReferenceNodeNotFoundException::class );

	    Group::make( 'group' )->insertBefore( 'nonExistentField', Text::make( 'textField' ) );
    }

    public function testNestedInsertBefore() {
	    $group = Group::make( 'group' )
		    ->append(
			    Text::make( 'topLevelTextField' ),
			    Group::make( 'nestedGroup' )
			         ->append(
				         Text::make( 'nestedTextField' )
			         )
		    )
		    ->insertBefore( 'nestedTextField', Text::make( 'anotherNestedTextField' ) );

	    $this->assertEquals( 0, $group->getNodeByName( 'nestedGroup' )->getNodeIndexByName( 'anotherNestedTextField' ) );
    }
}
