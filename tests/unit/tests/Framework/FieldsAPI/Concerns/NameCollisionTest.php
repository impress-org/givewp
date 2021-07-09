<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;
use Give\Framework\FieldsAPI\FieldCollection\Exception\NameCollisionException;

final class NameCollisionTest extends TestCase {

    public function testCheckNameCollision() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
        ]);

        $this->expectException( NameCollisionException::class );
        $collection->insertAfter( 'my-text-field', new FormField( 'text', 'my-text-field' ) );
    }

    public function testCheckNameCollisionDeep() {
        $collection = new FieldCollection( 'root', [
            new FormField( 'text', 'my-text-field' ),
        ]);

        $collection2 = new FieldCollection( 'nested', [
            new FormField( 'text', 'my-text-field' ),
        ]);

        $this->expectException( NameCollisionException::class );
        $collection->insertAfter( 'my-text-field', $collection2 );
    }
}