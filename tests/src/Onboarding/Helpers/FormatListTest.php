<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use FormatObjectList;

final class FormatListTest extends TestCase {

	public function testFromKeyValue(): void {
		$data          = [ 'foo' => 'bar' ];
		$formatter = FormatObjectList\Factory::fromKeyValue( $data );
		$formattedList = $formatter->format();
		$expectedList  = [
			[
				'value' => 'foo',
				'label' => 'bar',
			],
		];
		$this->assertEquals( $expectedList, $formattedList );
	}

	public function testFromValueKey(): void {
		$data          = [ 'foo' => 'bar' ];
		$formatter = FormatObjectList\Factory::fromValueKey( $data );
		$formattedList = $formatter->format();
		$expectedList  = [
			[
				'value' => 'bar',
				'label' => 'foo',
			],
		];
		$this->assertEquals( $expectedList, $formattedList );
	}
}
