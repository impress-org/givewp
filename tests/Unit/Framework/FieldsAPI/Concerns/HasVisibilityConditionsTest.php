<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasVisibilityConditions;
use Give\Framework\FieldsAPI\Conditions\BasicCondition;
use PHPUnit\Framework\TestCase;

final class HasVisibilityConditionsTest extends TestCase
{

    public function testCanAccessVisibilityConditions()
    {
        /** @var HasVisibilityConditions $mock */
        $mock = $this->getMockForTrait(HasVisibilityConditions::class);

		$this->assertEquals( [], $mock->getVisibilityConditions() );
	}

	public function testCanSetVisibilityCondition() {
		/** @var HasVisibilityConditions $mock */
		$mock = $this->getMockForTrait( HasVisibilityConditions::class );

		$mock->showIf( 'foo', '=', 'bar' );

		$this->assertCount( 1, $mock->getVisibilityConditions() );
	}

	public function testCanSetMultipleVisibilityConditions() {
		/** @var HasVisibilityConditions $mock */
		$mock = $this->getMockForTrait( HasVisibilityConditions::class );

		$mock->showWhen(
			new BasicCondition( 'foo', '=', 'bar' ),
			[ 'baz', '!=', 'foo' ]
		);

		$this->assertCount( 2, $mock->getVisibilityConditions() );
	}
}
