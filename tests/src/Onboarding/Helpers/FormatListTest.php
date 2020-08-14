<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Give\Onboarding\Helpers\FormatList;

final class FormatListTest extends TestCase {

	public function testFromKeyValue(): void {
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

	public function testFromValueKey(): void {
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
