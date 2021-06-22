<?php

use Give\Framework\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

final class TestFacade extends TestCase {

	public function testFacadeInheritance() {
		$this->assertEquals( 'hello', Hello::sayHello() );
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
