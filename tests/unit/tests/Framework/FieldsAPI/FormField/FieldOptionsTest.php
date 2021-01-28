<?php

use PHPUnit\Framework\TestCase;
use Give\Framework\FieldsAPI\FormField;

final class FieldOptionsTest extends TestCase {

    public function testFieldSupportsOptions() {

        $field = new FormField( 'text', 'my-text-field' );
        $this->assertFalse( $field->supportsOptions() );

        $field = new FormField( 'select', 'my-select-field' );
        $this->assertTrue( $field->supportsOptions() );
    }

    public function testAddOption() {
        $field = new FormField( 'select', 'my-select-field' );

        $field->addOption( 'aye', 'Aye' );
        $this->assertEquals( 1, count( $field->getOptions() ) );

        $field->addOption( 'bee', 'Bee' );
        $this->assertEquals( 2, count( $field->getOptions() ) );
    }

    public function testAddOptions() {
        $field = new FormField( 'select', 'my-select-field' );

        $field->addOptions([ 'aye' => 'Aye', 'bee' => 'Bee' ]);
        $this->assertEquals( 2, count( $field->getOptions() ) );

        $field->addOptions([ 'sea' => 'Sea' ]);
        $this->assertEquals( 3, count( $field->getOptions() ) );
    }
}
