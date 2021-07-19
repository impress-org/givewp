<?php

use Give\Framework\FieldsAPI\Factory\Field;
use Give_Unit_Test_Case;

class FieldTest extends Give_Unit_Test_Case {

	public function testMakeTextField() {
		$field = Field::text('my-text-field');

		$this->assertEquals( 'text', $field::TYPE );
	}
}
