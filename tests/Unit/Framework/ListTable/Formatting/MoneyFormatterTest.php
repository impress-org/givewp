<?php

namespace Give\Tests\Unit\Framework\ListTable\Formatting;

use Give\Framework\ListTable\Formatting\MoneyFormatter;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class MoneyFormatterTest extends TestCase
{
    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach (['currency', 'thousands_separator', 'decimal_separator', 'currency_position', 'auto_format_currency'] as $key) {
            give_delete_option($key);
        }

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testFormatsWithTheSiteCurrencySettings(): void
    {
        $this->assertSame('$1,331.28', MoneyFormatter::format(new Money(133128, 'USD')));
    }

    /**
     * @since TBD
     */
    public function testFollowsCommaDecimalSettingsWithoutMisreadingTheStoredDecimal(): void
    {
        give_update_option('currency', 'EUR');
        give_update_option('thousands_separator', '.');
        give_update_option('decimal_separator', ',');
        give_update_option('currency_position', 'after');

        $this->assertSame('1.331,28€', MoneyFormatter::format(new Money(133128, 'EUR')));
    }

    /**
     * @since TBD
     */
    public function testUsesTheAmountsOwnCurrency(): void
    {
        // Give formats euros with the euro's own separators (comma decimal), whatever the site currency is.
        $this->assertSame('€100,00', MoneyFormatter::format(new Money(10000, 'EUR')));
    }

    /**
     * @since TBD
     */
    public function testZeroDecimalCurrencyHasNoDecimals(): void
    {
        $this->assertSame('¥1,000', MoneyFormatter::format(new Money(1000, 'JPY')));
    }

    /**
     * @since TBD
     */
    public function testIgnoresTheAutoFormatSetting(): void
    {
        give_update_option('auto_format_currency', 'on');

        $this->assertSame('$1,331.28', MoneyFormatter::format(new Money(133128, 'USD')));
    }

    /**
     * @since TBD
     */
    public function testFromDecimalUsesTheSiteCurrency(): void
    {
        $this->assertSame('$12.50', MoneyFormatter::fromDecimal('12.5'));
    }

    /**
     * @since TBD
     */
    public function testMissingAmountIsTheDash(): void
    {
        $this->assertStringContainsString('givewp-list-table-dash', MoneyFormatter::format(null));
        $this->assertStringContainsString('No amount', MoneyFormatter::fromDecimal(null));
    }
}
