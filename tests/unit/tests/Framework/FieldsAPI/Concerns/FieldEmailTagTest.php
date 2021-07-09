<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FieldEmailTagTest extends TestCase {

    public function testHasEmailTag() {
        $field = new FormField( 'text', 'my-text-field' );
        $field->emailTag( 'myTextField' );
        $this->assertEquals( 'myTextField', $field->getEmailTag() );
    }
}
