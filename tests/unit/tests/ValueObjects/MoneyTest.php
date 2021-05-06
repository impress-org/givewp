<?php

use PHPUnit\Framework\TestCase;
use Give\ValueObjects\Money;

final class MoneyTest extends TestCase {

	public function testMinorAmountIsNotChanged() {
		$money = Money::of( 257.78, 'USD' );
		$this->assertEquals( 25778, $money->getMinorAmount() );
	}
}
