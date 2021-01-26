<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FormFieldTest extends TestCase {

    public function testHasType() {
        $field = new FormField( 'text', 'my-text-field' );
        $this->assertEquals( 'text', $field->getType() );
    }

    public function testHasName() {
        $field = new FormField( 'text', 'my-text-field' );
        $this->assertEquals( 'my-text-field', $field->getName() );
    }
}
