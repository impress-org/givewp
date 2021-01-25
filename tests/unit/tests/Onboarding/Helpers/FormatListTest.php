<?php

use PHPUnit\Framework\TestCase;
use Give\Onboarding\Helpers\FormatList;

final class FormatListTest extends TestCase {

	public function testFromKeyValue() {
		$data          = [ 'foo' => 'bar' ];
		$formattedList = FormatList::fromKeyValue( $data );
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
		$formattedList = FormatList::fromValueKey( $data );
		$expectedList  = [
			[
				'value' => 'bar',
				'label' => 'foo',
			],
		];
		$this->assertEquals( $expectedList, $formattedList );
	}
}
