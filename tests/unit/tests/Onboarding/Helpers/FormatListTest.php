<?php

use PHPUnit\Framework\TestCase;
use FormatObjectList;

final class FormatListTest extends TestCase {

	public function testFromKeyValue() {
		$data          = [ 'foo' => 'bar' ];
		$formattedList = FormatObjectList\Factory::fromKeyValue( $data );
		$expectedList  = [
			[
				'value' => 'foo',
				'label' => 'bar',
			],
		];
		$this->assertEquals( $expectedList, $formattedList );
	}

	public function testFromValueKey() {
		$data          = [ 'foo' => 'bar' ];
		$formattedList = FormatObjectList\Factory::fromValueKey( $data );
		$expectedList  = [
			[
				'value' => 'bar',
				'label' => 'foo',
			],
		];
		$this->assertEquals( $expectedList, $formattedList );
	}
}
