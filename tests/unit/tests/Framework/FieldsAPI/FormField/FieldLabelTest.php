<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FieldLabelTest extends TestCase {

    public function testHasLabel() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->label( 'My Textfield' );
        $this->assertEquals( 'My Textfield' , $field->getLabel() );
    }
}
