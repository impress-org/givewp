<?php

use Give\Framework\FieldsAPI\Factory;
use Give\Framework\FieldsAPI\Text;

class FactoryTest extends Give_Unit_Test_Case {

	public function testMakeTextField() {
		$field = Factory::text('my-text-field');

		$this->assertInstanceOf(Text::class, $field);
		$this->assertEquals('my-text-field', $field->getName());
	}
}
