<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;

final class RemoveNodeTest extends TestCase {
    
    public function testRemoveNode() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
            new FormField( 'text', 'my-second-text-field' ),
        ]);

        $collection->remove( 'my-second-text-field' );

        $count = 0;
        $collection->walk( function( $field ) use ( &$count ) {
            $count++;
        });

        $this->assertEquals( 1, $count );
    }
}
