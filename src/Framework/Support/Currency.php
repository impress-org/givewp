<?php

namespace Give\Framework\Support;

use Give\Framework\Support\Facades\CurrencyFacade;
use Give\Framework\Support\Facades\Facade;
use Money\Money;

/**
 * @method static Money convertToBaseCurrency(Money $amount, int|float|string $exchangeRate = 1)
 * @method static int|float formatAsLegacyAmount(Money $amount)
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
