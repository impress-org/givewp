<?php

use Give\Framework\FieldsAPI\Types;
use PHPUnit\Framework\TestCase;

final class TypesTest extends TestCase {

	public function testCanGetAllTypes() {
		$this->assertContainsOnly( 'string', Types::all() );
	}
}
