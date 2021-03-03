<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;
use Give\Framework\FieldsAPI\FieldCollection\Exception\ReferenceNodeNotFoundException;

final class InsertNodeTest extends TestCase {

    public function testInsertAfter() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
        ]);
        $collection->insertAfter( 'my-text-field', new FormField( 'text', 'my-second-text-field' ) );
        $this->assertEquals( 1, $collection->getNodeIndexByName( 'my-second-text-field' ) );
    }

    public function testInsertAfterReferenceNotFound() {
        $collection = new FieldCollection( 'root' );
        $this->expectException( ReferenceNodeNotFoundException::class );
        $collection->insertAfter( 'my-non-existant-text-field', new FormField( 'text', 'my-text-field' ) );
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

    public function testInsertBeforeReferenceNotFound() {
        $collection = new FieldCollection( 'root' );
        $this->expectException( ReferenceNodeNotFoundException::class );
        $collection->insertBefore( 'my-non-existant-text-field', new FormField( 'text', 'my-text-field' ) );
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
}
