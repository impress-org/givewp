<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FieldDefaultValueTest extends TestCase {

    public function testDefaultValue() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->defaultValue( 'Hello world' );
        $this->assertEquals( 'Hello world' , $field->getDefaultValue() );
    }
}
