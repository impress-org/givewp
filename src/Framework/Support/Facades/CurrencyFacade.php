<?php

namespace Give\Framework\Support\Facades;

use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exchange\FixedExchange;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;
use NumberFormatter;

class CurrencyFacade
{
    /**
     * Immutably converts the given amount into the system currency.
     *
     * @since 2.20.0
     *
     * @param Money $amount
     * @param int|float $exchangeRate
     *
     * @return Money
     */
    public function convertToBaseCurrency(Money $amount, $exchangeRate = 1)
    {
        $baseCurrency = $this->getBaseCurrency();

        if ($amount->getCurrency()->equals($baseCurrency)) {
            return $amount;
        }

        $converter = new Converter(
            new ISOCurrencies(), new FixedExchange([
                $amount->getCurrency()->getCode() => [
                    give_get_option('currency', 'USD') => $exchangeRate,
                ],
            ])
        );

        return $converter->convert($amount, $baseCurrency);
    }

    /**
     * Creates a new Money instance from a decimal amount
     *
     * @since 2.20.0
     *
     * @param string|float|int $amount
     * @param string $currency
     *
     * @return Money
     */
    public function parseFromDecimal($amount, $currency)
    {
        return (new DecimalMoneyParser(new ISOCurrencies()))->parse((string)$amount, new Currency($currency));
    }

    /**
     * Returns the amount in a decimal format, not including any currency symbols:
     * - $1,500.25 -> 1500.25
     *
     * @since 2.20.0
     *
     * @param Money $amount
     *
     * @return string
     */
    public function formatToDecimal(Money $amount)
    {
        return (new DecimalMoneyFormatter(new ISOCurrencies()))->format($amount);
    }

    /**
     * Formats the amount to a currency format, including currency symbols, in the given locale.
     *
     * @since 2.20.0
     *
     * @param Money $amount
     * @param string|null $locale
     *
     * @return string
     */
    public function formatToLocale(Money $amount, $locale = null)
    {
        if ($locale === null) {
            $locale = get_locale();
        }

        $numberFormatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($amount);
    }

    /**
     * Retrieves the system's base currency
     *
     * @since 2.20.0
     *
     * @return Currency
     */
    public function getBaseCurrency()
    {
        return new Currency(give_get_option('currency', 'USD'));
    }
}
