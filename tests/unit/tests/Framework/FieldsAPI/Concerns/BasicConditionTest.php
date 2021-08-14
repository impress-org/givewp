<?php

use Give\Framework\FieldsAPI\Concerns\BasicCondition;
use PHPUnit\Framework\TestCase;

final class BasicConditionTest extends TestCase {

	public function testOperatorCanBeInverted() {
		$mock = new BasicCondition('field', '=', 'value');

		$mock->invert();

		$this->assertEquals( '!=', $mock->operator );

		$mock->invert();

		$this->assertEquals( '=', $mock->operator );
	}
}
