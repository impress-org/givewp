<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FieldStoreAsMetaTest extends TestCase {

    public function testStoreAsDonorMeta() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->storeAsDonorMeta();
        $this->assertTrue( $field->shouldStoreAsDonorMeta() );
    }

    public function testNotStoreAsDonorMeta() {
        $field = new FormField( 'text', 'my-text-field' );

        // False by default.
        $this->assertFalse( $field->shouldStoreAsDonorMeta() );

        $field->storeAsDonorMeta(); // Toggle on.
        $field->storeAsDonorMeta( false ); // Toggle off.
        $this->assertFalse( $field->shouldStoreAsDonorMeta() );
    }

    public function testStoreAsMetaMethodEnforcesBooleanType() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->storeAsDonorMeta( 'foo' );

        $this->assertInternalType( 'bool', $field->shouldStoreAsDonorMeta() );
    }
}
