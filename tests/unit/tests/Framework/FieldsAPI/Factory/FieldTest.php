<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\Factory\Field;

final class FieldTest extends TestCase {

    public function testMakeTextField() {
        $field = Field::text( 'my-text-field' );
        $this->assertEquals( 'text', $field->getType() );
    }

    public function testMakeSelectField() {
        $field = Field::select( 'my-select-field' );
        $this->assertEquals( 'select', $field->getType() );
    }

    public function testMakeTextareaField() {
        $field = Field::textarea( 'my-textarea-field' );
        $this->assertEquals( 'textarea', $field->getType() );
    }
}
