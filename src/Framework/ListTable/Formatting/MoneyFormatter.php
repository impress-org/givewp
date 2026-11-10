<?php

namespace Give\Framework\ListTable\Formatting;

use Give\Framework\ListTable\Views\Placeholder;
use Give\Framework\Support\Currencies\GiveCurrencies;
use Give\Framework\Support\ValueObjects\Money;
use Money\Currencies\AggregateCurrencies;
use Money\Currencies\BitcoinCurrencies;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exception\UnknownCurrencyException;

/**
 * The one way list tables write money. It follows the currency settings of the site, the same way
 * receipts and reports do.
 *
 * @since TBD
 */
final class MoneyFormatter
{
    /**
     * @var AggregateCurrencies|null
     */
    private static $currencies;

    /**
     * Writes the amount as text. A missing amount comes back as the dash placeholder, which is an HTML
     * string, so use the result in HTML cells and not as plain text (a CSV, an aria-label).
     *
     * @since TBD
     */
    public static function format(?Money $amount): string
    {
        if ($amount === null) {
            return Placeholder::dash(__('No amount', 'give'));
        }

        $code = $amount->getCurrency()->getCode();

        // Format the size of the amount, then put the minus sign in front. Give's own helpers drop the sign
        // or put it after the currency symbol, depending on the amount. The decimal text is already clean, so
        // it must not be sanitized again: with "." as the thousands separator, "12.50" would be read as 1250.
        $decimal = $amount->formatToDecimal();
        $isNegative = strpos($decimal, '-') === 0;

        $text = give_currency_filter(
            give_format_amount(ltrim($decimal, '-'), ['currency' => $code, 'sanitize' => false]),
            ['currency_code' => $code, 'decode_currency' => true]
        );

        return $isNegative ? '-' . $text : $text;
    }

    /**
     * Like format(), for a decimal amount. A missing amount comes back as the dash placeholder (HTML).
     *
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
        try {
            $decimals = self::currencies()->subunitFor(new Currency($currency));
        } catch (UnknownCurrencyException $exception) {
            // A stored code that none of the sets know must not fail a whole response.
            $decimals = (int) give_get_price_decimals($currency);
        }

        return number_format($amount, $decimals, '.', '');
    }

    /**
     * The currency sets are built once, not once for every cell.
     */
    private static function currencies(): AggregateCurrencies
    {
        if (self::$currencies === null) {
            self::$currencies = new AggregateCurrencies([
                new GiveCurrencies(),
                new ISOCurrencies(),
                new BitcoinCurrencies(),
            ]);
        }

        return self::$currencies;
    }

    /**
     * The unformatted amount for a row's "rawValues" key: ['amount' => '1331.28', 'currency' => 'USD'].
     *
     * @since TBD
     *
     * @return array{amount: string, currency: string}|null
     */
    public static function raw(?Money $amount): ?array
    {
        if ($amount === null) {
            return null;
        }

        return [
            'amount' => $amount->formatToDecimal(),
            'currency' => $amount->getCurrency()->getCode(),
        ];
    }
}
