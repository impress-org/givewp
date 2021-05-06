<?php

use PHPUnit\Framework\TestCase;
use Give\ValueObjects\Money;

final class MoneyTest extends TestCase {

	public function testMinorAmount() {
		$money = Money::of( 250, 'USD' );
		$this->assertEquals( 25000, $money->getMinorAmount() );
	}

	public function testMinorAmountFloat() {

		$this->assertEquals(
			7446,
			Money::of( 74.46, 'USD' )->getMinorAmount()
		);

		$this->assertEquals(
			25778,
			Money::of( 257.78, 'USD' )->getMinorAmount()
		);
	}
}
