<?php

use Give\Framework\FieldsAPI\Concerns\IsRepeatable;
use PHPUnit\Framework\TestCase;

final class IsRepeatableTest extends TestCase {

	public function testIsRepeatable() {
		/** @var IsRepeatable $mock */
		$mock = $this->getMockForTrait( IsRepeatable::class );

		// Default is false
		$this->assertFalse( $mock->isRepeatable() );

		// Try setting true
		$mock->repeatable();
		$this->assertTrue( $mock->isRepeatable() );

		// Try setting back to false
		$mock->repeatable( false );
		$this->assertFalse( $mock->isRepeatable() );
	}
}
