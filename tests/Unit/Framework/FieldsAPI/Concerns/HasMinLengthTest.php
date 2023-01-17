<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasMinLength;
use Give\Framework\FieldsAPI\Concerns\ValidationRules;
use PHPUnit\Framework\TestCase;

final class HasMinLengthTest extends TestCase
{

    public function testHasMinLength()
    {
        $mock = new HasMinLengthMock();

        $mock->minLength( 8 );

		$this->assertEquals( 8, $mock->getMinLength() );

		$mock->minLength( 16 );

		$this->assertEquals( 16, $mock->getMinLength() );
	}
}

final class HasMinLengthMock {
	use HasMinLength;

	protected $validationRules;

	public function __construct() {
		$this->validationRules = new ValidationRules();
	}
}

