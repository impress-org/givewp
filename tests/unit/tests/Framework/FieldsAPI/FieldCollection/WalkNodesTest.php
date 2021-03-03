<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\Factory\Field;
use Give\Framework\FieldsAPI\FieldCollection;

final class WalkNodesTest extends TestCase {

    public function testWalk() {
        $fieldCollection = new FieldCollection( 'root', [
            Field::text( 'my-text-field' ),
            Field::text( 'my-second-text-field' ),
            Field::text( 'my-third-text-field' ),
            Field::text( 'my-fourth-text-field' ),
        ]);

        $count = 0;
        $fieldCollection->walk(function( $field ) use ( &$count ) {
            $count++;
        });

        $this->assertEquals( 4, $count );
    }

    public function testNestedWalk() {
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
