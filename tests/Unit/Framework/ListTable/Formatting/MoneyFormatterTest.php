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
     * A float total, like a SUM() from the database, reads the same as its decimal string.
     *
     * @since TBD
     */
    public function testFromDecimalAcceptsFloats(): void
    {
        $this->assertSame('$12.50', MoneyFormatter::fromDecimal(12.5));
        $this->assertSame('$1,234.50', MoneyFormatter::fromDecimal(1234.5));
        $this->assertSame('$0.30', MoneyFormatter::fromDecimal(0.1 + 0.2));
        $this->assertSame('¥1,000', MoneyFormatter::fromDecimal(1000.4, 'JPY'));
    }

    /**
     * @since TBD
     */
    public function testNegativeAmountsKeepTheirSign(): void
    {
        $this->assertSame('-$12.50', MoneyFormatter::fromDecimal(-12.5));
        $this->assertSame('-$1,234.50', MoneyFormatter::fromDecimal('-1234.50'));
    }

    /**
     * On PHP 7.4, casting a float to a string follows LC_NUMERIC, so a comma locale writes "12,5".
     * The formatter must not depend on that.
     *
     * @since TBD
     */
    public function testFromDecimalWithFloatIgnoresTheNumericLocale(): void
    {
        $previous = setlocale(LC_NUMERIC, '0');
        $commaLocale = setlocale(LC_NUMERIC, 'de_DE.UTF-8', 'de_DE.utf8', 'de_DE', 'pt_BR.UTF-8', 'pt_BR.utf8', 'fr_FR.UTF-8');

        if ($commaLocale === false) {
            $this->markTestSkipped('No locale with a comma decimal point is installed.');
        }

        try {
            $this->assertSame('$1,234.50', MoneyFormatter::fromDecimal(1234.5));
        } finally {
            setlocale(LC_NUMERIC, $previous);
        }
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
