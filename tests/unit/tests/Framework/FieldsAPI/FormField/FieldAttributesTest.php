<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FieldAttributesTest extends TestCase {

    public function testHasAttributes() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->attributes( $attributes = [
            'foo' => 'bar',
        ]);
        $this->assertEquals( $attributes, $field->getAttributes() );
    }
}
