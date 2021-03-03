<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FieldReadOnlyTest extends TestCase {

    public function testReadyOnlyDefault() {
        $field = new FormField( 'text', 'my-text-field' );
        $this->assertFalse( $field->isReadOnly() );
    }

    public function testReadyOnlyEnable() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->readOnly();
        $this->assertTrue( $field->isReadOnly() );
    }

    public function testReadyOnlyDisable() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->readOnly();
        $field->readOnly( false );
        $this->assertFalse( $field->isReadOnly() );
    }
}
