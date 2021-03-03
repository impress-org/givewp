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
    }
}
