<?php

use PHPUnit\Framework\TestCase;
use Give\ValueObjects\Money;

final class MoneyTest extends TestCase {

	public function testMinorAmount() {
		$money = Money::of( 250, 'USD' );
		$this->assertEquals( 25000, $money->getMinorAmount() );
	}

	public function testMinorAmountFloat() {
		$money = Money::of( 257.78, 'USD' );
		$this->assertEquals( 25778, $money->getMinorAmount() );
	}
}
