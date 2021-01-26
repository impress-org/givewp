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
}
