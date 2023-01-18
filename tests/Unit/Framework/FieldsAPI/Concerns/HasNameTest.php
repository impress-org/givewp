<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\HasName;
use PHPUnit\Framework\TestCase;

final class HasNameTest extends TestCase
{

    public function testHasName()
    {
        $mock = new HasNameMock('Name');
        $this->assertEquals('Name', $mock->getName());
    }
}

final class HasNameMock {
	use HasName;

	public function __construct( $name ) {
		$this->name = $name;
	}
}
