<?php

namespace Give\Framework\Support\Facades;

use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exchange\FixedExchange;
use Money\Money;

class CurrencyFacade
{
    public function convertToBaseCurrency(Money $amount, $exchangeRate = 1)
    {
        $baseCurrency = $this->getBaseCurrency();

        if ( $amount->getCurrency()->equals($baseCurrency) ) {
            return $amount;
        }

        $converter = new Converter(new ISOCurrencies(), new FixedExchange([
            give_get_option('currency', 'USD') => [
                $amount->getCurrency()->getCode() => $exchangeRate,
            ]
        ]));

        return $converter->convert($amount, $baseCurrency);
    }

    /**
     * Formats the
     *
     * @param Money $amount
     *
     * @return string
     */
    public function formatAsLegacyAmount(Money $amount)
    {
        $numberDecimals = give_get_currencies('setting')[$amount->getCurrency()->getCode()]['number_decimals'];

        return substr_replace($amount->getAmount(), '.', $numberDecimals, 0);
    }

    /**
     * @return Currency
     */
    public function getBaseCurrency()
    {
        return new Currency(give_get_option('currency', 'USD'));
    }
}
