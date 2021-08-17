<?php

use Give\Framework\FieldsAPI\Concerns\HasVisibilityConditions;
use Give\Framework\FieldsAPI\Conditions\BasicCondition;
use PHPUnit\Framework\TestCase;

final class HasVisibilityConditionsTest extends TestCase {

	public function testCanAccessVisibilityConditions() {
		/** @var HasVisibilityConditions $mock */
		$mock = $this->getMockForTrait( HasVisibilityConditions::class );

		$this->assertEquals( [], $mock->getVisibilityConditions() );
	}

	public function testCanSetWhenShown() {
		/** @var HasVisibilityConditions $mock */
		$mock = $this->getMockForTrait( HasVisibilityConditions::class );
		$mockCondition = new BasicCondition( 'foo', '=', 'bar' );

		$mock->showIf( $mockCondition );

		$this->assertCount( 1, $mock->getVisibilityConditions() );

		$mock->showIf( $mockCondition, $mockCondition );

		$this->assertCount( 3, $mock->getVisibilityConditions() );
	}
}
