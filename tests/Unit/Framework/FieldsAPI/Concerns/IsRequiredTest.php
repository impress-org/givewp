<?php
namespace GiveTests\Unit\Framework\FieldsAPI\Concerns;

use Give\Framework\FieldsAPI\Concerns\IsRequired;
use Give\Framework\FieldsAPI\Concerns\ValidationRules;
use PHPUnit\Framework\TestCase;

final class FieldRequiredTest extends TestCase
{

    public function testRequiredDefault()
    {
        $mock = new IsRequiredMock();
        $this->assertFalse($mock->isRequired());
    }

    public function testRequiredEnable() {
        $mock = new IsRequiredMock();
        $mock->required();
        $this->assertTrue( $mock->isRequired() );
    }

    public function testRequiredDisable() {
        $mock = new IsRequiredMock();
        $mock->required();
        $mock->required( false );
        $this->assertFalse( $mock->isRequired() );
    }
}

final class IsRequiredMock {
	use IsRequired;

	protected $validationRules;

	public function __construct() {
		$this->validationRules = new ValidationRules();
	}
}
