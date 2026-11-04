<?php

namespace Give\Framework\ListTable\Formatting;

use Give\Framework\ListTable\Views\Placeholder;
use Give\Framework\Support\Currencies\GiveCurrencies;
use Give\Framework\Support\ValueObjects\Money;
use Money\Currencies\AggregateCurrencies;
use Money\Currencies\BitcoinCurrencies;
use Money\Currencies\ISOCurrencies;
use Money\Currency;

/**
 * The one way list tables write money. It follows the currency settings of the site, the same way
 * receipts and reports do.
 *
 * @since TBD
 */
final class MoneyFormatter
{
    /**
     * @since TBD
     */
    public static function format(?Money $amount): string
    {
        if ($amount === null) {
            return Placeholder::dash(__('No amount', 'give'));
        }

        $code = $amount->getCurrency()->getCode();

        return give_currency_filter(
            give_format_amount($amount->formatToDecimal(), ['currency' => $code]),
            ['currency_code' => $code, 'decode_currency' => true]
        );
    }

    /**
     * @since TBD
     *
     * @param string|int|float|null $amount A decimal amount such as "12.50", or a number.
     * @param string|null $currency Defaults to the currency of the site.
     */
    public static function fromDecimal($amount, ?string $currency = null): string
    {
        if ($amount === null || $amount === '') {
            return Placeholder::dash(__('No amount', 'give'));
        }

        $currency = $currency ?: give_get_currency();

        if (is_float($amount)) {
            $amount = self::floatToDecimalString($amount, $currency);
        }

        return self::format(Money::fromDecimal($amount, $currency));
    }

    /**
     * Writes a float as a plain decimal string, like "1234.50": a dot for the decimal point, no
     * thousands separator, and as many decimals as the currency has. A plain (string) cast is not
     * safe here, because on PHP 7.4 it follows the server locale and can write "1234,5".
     *
     * @since TBD
     */
    private static function floatToDecimalString(float $amount, string $currency): string
    {
        $currencies = new AggregateCurrencies([
            new GiveCurrencies(),
            new ISOCurrencies(),
            new BitcoinCurrencies(),
        ]);

        return number_format($amount, $currencies->subunitFor(new Currency($currency)), '.', '');
    }
}
