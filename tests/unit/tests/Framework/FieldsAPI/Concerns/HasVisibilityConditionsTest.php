<?php

use Give\Framework\FieldsAPI\Concerns\Condition;
use Give\Framework\FieldsAPI\Concerns\HasVisibilityConditions;
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

		/** @var Condition $mockCondition */
		$mockCondition = $this->getMockForAbstractClass( Condition::class );

		$mock->showIf( $mockCondition );

		$this->assertCount( 1, $mock->getVisibilityConditions() );

		$mock->showIf( $mockCondition, $mockCondition );

		$this->assertCount( 3, $mock->getVisibilityConditions() );
	}

	public function testCanSetWhenHidden() {
		/** @var HasVisibilityConditions $mock */
		$mock = $this->getMockForTrait( HasVisibilityConditions::class );

		/** @var Condition $mockCondition */
		$mockCondition = $this->getMockForAbstractClass( Condition::class );

		$mock->hideIf( $mockCondition );

		$this->assertCount( 1, $mock->getVisibilityConditions() );

		$mock->hideIf( $mockCondition, $mockCondition, $mockCondition );

		$this->assertCount( 4, $mock->getVisibilityConditions() );
	}
}
