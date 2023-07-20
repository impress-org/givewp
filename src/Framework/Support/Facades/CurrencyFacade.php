<?php

namespace Give\Framework\Support\Facades;

use Give\Log\Log;
use Money\Converter;
use Money\Currencies;
use Money\Currencies\AggregateCurrencies;
use Money\Currencies\BitcoinCurrencies;
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
     * @since 2.27.3 updated to use aggregated currency list.
     *
     * @since 2.20.0
     *
     * @param Money $amount
     * @param int|float $exchangeRate
     */
    public function convertToBaseCurrency(Money $amount, $exchangeRate = 1): Money
    {
        $baseCurrency = $this->getBaseCurrency();

        if ($amount->getCurrency()->equals($baseCurrency)) {
            return $amount;
        }

        $converter = new Converter(
            $this->getCurrenciesList(), new FixedExchange([
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
     * @since 2.27.3 updated to use aggregated currency list.
     *
     * @since 2.20.0
     *
     * @param string|float|int $amount
     */
    public function parseFromDecimal($amount, string $currency): Money
    {
        return (new DecimalMoneyParser($this->getCurrenciesList()))->parse((string)$amount, new Currency($currency));
    }

    /**
     * Returns the amount in a decimal format, not including any currency symbols:
     * - $1,500.25 -> 1500.25
     *
     * @since 2.20.0
     */
    public function formatToDecimal(Money $amount): string
    {
        return (new DecimalMoneyFormatter($this->getCurrenciesList()))->format($amount);
    }

    /**
     * Formats the amount to a currency format, including currency symbols, in the given locale.
     *
     * @since 2.27.3 updated to use aggregated currency list.
     *
     * @since 2.24.2 fallback on give formatting system if intl extension is not available
     * @since 2.20.0
     *
     * @param Money $amount
     * @param string|null $locale
     *
     * @return string
     */
    public function formatToLocale(Money $amount, $locale = null): string
    {
        $useAutoFormatting = give_get_option('auto_format_currency');
        if (!class_exists(NumberFormatter::class) || !$useAutoFormatting) {
            if ($useAutoFormatting) {
                Log::warning(
                    'Auto-formatting is enabled  at Donations > Settings > General > Currency but the INTL extension for PHP is not available. Please install the INTL extension to enable auto-formatting, or disable the Auto-formatting setting to prevent this error message. Most web hosts can help with installing and activating INTL. GiveWP is falling back to formatting based on the legacy settings.'
                );
            }

            return give_currency_filter(
                $this->formatToDecimal($amount),
                ['currency' => $amount->getCurrency()->getCode()]
            );
        }

        if ($locale === null) {
            $locale = get_locale();
        }

        $numberFormatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $this->getCurrenciesList());

        return $moneyFormatter->format($amount);
    }

    /**
     * Retrieves the system's base currency
     *
     * @since 2.20.0
     *
     * @return Currency
     */
    public function getBaseCurrency(): Currency
    {
        return new Currency(give_get_option('currency', 'USD'));
    }

    /**
     * Retrieves a list for all supported currencies.
     *
     * @since 2.27.3
     */
    private function getCurrenciesList(): Currencies
    {
        return new AggregateCurrencies([
            new ISOCurrencies(),
            new BitcoinCurrencies(),
        ]);
    }
}
