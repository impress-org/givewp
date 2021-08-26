<?php

use Give\Framework\FieldsAPI\Concerns\IsRepeatable;
use Give\Framework\FieldsAPI\Text;
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

	public function testIsRepeatableValidation() {
		$mock = Text::make( 'text' )->repeatable();

		$this->assertNull( $mock->getMaxRepeatable() );

		$mock->maxRepeatable( 4 );

		$this->assertEquals( 4, $mock->getMaxRepeatable() );
	}
}
