<?php
namespace Give\Tests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\IsReadOnly;
use PHPUnit\Framework\TestCase;

class IsReadOnlyTest extends TestCase
{

    public function testReadOnlyDefault()
    {
        /** @var IsReadOnly $mock */
        $mock = $this->getMockForTrait(IsReadOnly::class);
        $this->assertFalse($mock->isReadOnly());
	}

	public function testReadOnlyEnable() {
		/** @var IsReadOnly $mock */
		$mock = $this->getMockForTrait( IsReadOnly::class );
		$mock->readOnly();
		$this->assertTrue( $mock->isReadOnly() );
	}

	public function testReadOnlyDisable() {
		/** @var IsReadOnly $mock */
		$mock = $this->getMockForTrait( IsReadOnly::class );
		$mock->readOnly( false );
		$this->assertFalse( $mock->isReadOnly() );
	}
}
