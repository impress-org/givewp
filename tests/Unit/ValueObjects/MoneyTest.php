<?php

namespace Give\Tests\Unit\ValueObjects;

use Give\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{

    public function testOfAmount()
    {
        $money = Money::of(250, 'USD');
        $this->assertEquals(250, $money->getAmount());
        $this->assertEquals(25000, $money->getMinorAmount());
    }

    public function testOfMinorAmount() {
        $money = Money::ofMinor( 25000, 'USD' );
        $this->assertEquals( 250, $money->getAmount() );
        $this->assertEquals( 25000, $money->getMinorAmount() );
    }

    public function testOfAmountFloat() {
        $money = Money::of( 250.56, 'USD' );
        $this->assertEquals( 250.56, $money->getAmount() );
        $this->assertEquals( 25056, $money->getMinorAmount() );
    }

    public function testOfAmountZeroBasedCurrency() {
        $money = Money::of( 25000, 'JPY' );
        $this->assertEquals( 25000, $money->getAmount() );
        $this->assertEquals( 25000, $money->getMinorAmount() );
    }
}
