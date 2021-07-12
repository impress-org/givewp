<?php

use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Text;
use PHPUnit\Framework\TestCase;

final class MoveNodeTest extends TestCase {

    public function testMoveAfter() {
	    $form = Form::make( 'form' )
		    ->append(
			    Text::make( 'firstTextField' ),
			    Text::make( 'secondTextField' )
		    );

	    $form->move( 'firstTextField' )->after( 'secondTextField' );

	    $this->assertEquals( 1, $form->getNodeIndexByName( 'firstTextField' ) );
	    $this->assertEquals( 0, $form->getNodeIndexByName( 'secondTextField' ) );
    }
}
