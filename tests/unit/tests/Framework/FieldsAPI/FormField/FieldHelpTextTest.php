<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FieldHelpTextTest extends TestCase {

    public function testHasLabel() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->helpText( 'This is my text field.' );
        $this->assertEquals( 'This is my text field.' , $field->getHelpText() );
    }
}
