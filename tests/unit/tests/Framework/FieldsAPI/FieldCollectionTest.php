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

    public function testInsertAfter() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
        ]);
        $collection->insertAfter( 'my-text-field', new FormField( 'text', 'my-second-text-field' ) );
        $this->assertEquals( 1, $collection->getNodeIndexByName( 'my-second-text-field' ) );
    }

    public function testNestedInsertAfter() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
            new FieldCollection( 'nested', [
                new FormField( 'text', 'my-second-text-field' )
            ]),
        ]);
        $collection->insertAfter( 'my-second-text-field',  new FormField( 'text', 'my-third-text-field' ) );

        $nodes = $collection->getFields();
        $nestedCollection = $nodes[ $collection->getNodeIndexByName( 'nested' ) ];
        $this->assertEquals( 1, $nestedCollection->getNodeIndexByName( 'my-third-text-field' ) );
    }

    public function testInsertBefore() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
        ]);
        $collection->insertBefore( 'my-text-field', new FormField( 'text', 'my-second-text-field' ) );
        $this->assertEquals( 0, $collection->getNodeIndexByName( 'my-second-text-field' ) );
    }

    public function testNestedInsertBefore() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
            new FieldCollection( 'nested', [
                new FormField( 'text', 'my-second-text-field' )
            ]),
        ]);
        $collection->insertBefore( 'my-second-text-field',  new FormField( 'text', 'my-third-text-field' ) );

        $nodes = $collection->getFields();
        $nestedCollection = $nodes[ $collection->getNodeIndexByName( 'nested' ) ];
        $this->assertEquals( 0, $nestedCollection->getNodeIndexByName( 'my-third-text-field' ) );
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
