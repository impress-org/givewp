<?php

use Give\Framework\FieldsAPI\Concerns\BasicCondition;
use Give\Framework\FieldsAPI\Concerns\NestedCondition;
use PHPUnit\Framework\TestCase;

final class NestedConditionTest extends TestCase {

	public function testOperatorCanBeInverted() {
		$mock = new NestedCondition(
			[
				new BasicCondition( 'foo', '=', 'baz' ),
				new BasicCondition( 'quz', '>', 'bar' ),
			]
		);

		$mock->invert();

		$this->assertEquals( '!=', $mock->conditions[0]->operator );
		$this->assertEquals( '<=', $mock->conditions[1]->operator );
	}
}
