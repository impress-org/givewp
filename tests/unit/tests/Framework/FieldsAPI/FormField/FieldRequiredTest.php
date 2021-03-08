<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FieldRequiredTest extends TestCase {

    public function testRequiredDefault() {
        $field = new FormField( 'text', 'my-text-field' );
        $this->assertFalse( $field->isRequired() );
    }

    public function testRequiredEnable() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->required();
        $this->assertTrue( $field->isRequired() );
    }

    public function testRequiredDisable() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->required();
        $field->required( false );
        $this->assertFalse( $field->isRequired() );
    }
}
