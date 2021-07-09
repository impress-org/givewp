<?php

use Give\Framework\FieldsAPI\Concerns\HasName;
use PHPUnit\Framework\TestCase;

final class HasNameTest extends TestCase {

	public function testHasName() {
		$mock = new UsesHasName( 'Name' );
		$this->assertEquals( 'Name', $mock->getName() );
	}
}

final class UsesHasName {
	use HasName;

	public function __construct( $name ) {
		$this->name = $name;
	}
}
