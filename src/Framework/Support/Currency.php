<?php

namespace Give\Framework\Support;

use Give\Framework\Support\Facades\CurrencyFacade;
use Give\Framework\Support\Facades\Facade;
use Money\Money;

/**
 * @method static Money convertToBaseCurrency(Money $amount, int|float|string $exchangeRate = 1)
 * @method static Money parseFromDecimal(string|float|int $amount, string $currencyCode)
 * @method static string formatToDecimal(Money $amount)
 * @method static string formatToLocale(Money $amount, $local = null)
 * @method static \Money\Currency getBaseCurrency()
 */
class Currency extends Facade
{
    /**
     * @inheritDoc
     */
    protected function getFacadeAccessor()
    {
        return CurrencyFacade::class;
    }
}
