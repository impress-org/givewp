<?php

use Give\Framework\FieldsAPI\Factory;
use Give_Unit_Test_Case;

class FactoryTest extends Give_Unit_Test_Case {

	public function testMakeTextField() {
		$field = Factory::text('my-text-field');

		$this->assertEquals( 'text', $field::TYPE );
	}
}
