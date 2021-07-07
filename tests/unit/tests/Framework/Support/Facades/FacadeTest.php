<?php

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

final class FacadeTest extends TestCase {
	public function testFacadeInheritance() {
		$this->assertEquals( 'hello', Hello::sayHello() );
	}

	public function testFacadeClassRequirement() {
		$this->expectException( Exception::class );
		FailsToSetClass::foo();
	}
}

/**
 * Class HelloGenerator
 */
class HelloGenerator {
	public function sayHello() {
		return 'hello';
	}
}

/**
 * Class Hello
 * @method static sayHello()
 */
class Hello extends Facade {
	protected static function getFacadeClass() {
		return HelloGenerator::class;
	}
}

class FailsToSetClass extends Facade {
}
