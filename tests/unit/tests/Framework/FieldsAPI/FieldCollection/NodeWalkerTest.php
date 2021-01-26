<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\Factory\Field;
use Give\Framework\FieldsAPI\FieldCollection;

final class NodeWalkerTest extends TestCase {

    public function testNodeWalker() {
        $fieldCollection = new FieldCollection( 'root', [
            Field::text( 'my-text-field' ),
            new FieldCollection( 'nested', [
                Field::text( 'my-third-text-field' ),
                Field::text( 'my-fourth-text-field' ),
            ]),
            Field::text( 'my-second-text-field' ),
        ]);

        $count = 0;
        $fieldCollection->walk(function( $field ) use ( &$count ) {
            $count++;
        });

        $this->assertEquals( 4, $count );
    }
}
