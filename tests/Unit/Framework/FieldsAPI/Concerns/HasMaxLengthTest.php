<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasMaxLength;
use Give\Vendors\StellarWP\Validation\Concerns\HasValidationRules;
use PHPUnit\Framework\TestCase;

final class HasMaxLengthTest extends TestCase
{
    public function testHasMaxLength()
    {
        $mock = new HasMaxLengthMock();

        $mock->maxLength( 8 );

		$this->assertEquals( 8, $mock->getMaxLength() );

		$mock->maxLength( 16 );

		$this->assertEquals( 16, $mock->getMaxLength() );
	}
}

final class HasMaxLengthMock {
	use HasMaxLength;
    use HasValidationRules;
}
