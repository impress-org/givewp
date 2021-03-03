<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;

final class FieldCollectionTest extends TestCase {

    public function testHasName() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
        ]);
        $this->assertEquals( 'root', $collection->getName() );
    }

    public function testGetNodeByName() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
            new FormField( 'text', 'my-second-text-field' ),
        ]);

        $node = $collection->getNodeByName( 'my-second-text-field' );

        $this->assertEquals( $node->getName(), 'my-second-text-field' );
    }

    public function testGetNestedNodeByName() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
            new FieldCollection( 'nested', [
                new FormField( 'text', 'my-second-text-field' ),
            ]),
        ]);

        $node = $collection->getNodeByName( 'my-second-text-field' );

        $this->assertEquals( $node->getName(), 'my-second-text-field' );
    }
}
