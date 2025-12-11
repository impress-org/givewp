<?php

namespace Give\Framework\Support\Currencies;

use Money\Currencies;
use Money\Currency;
use Money\Exception\UnknownCurrencyException;

/**
 * Custom currencies implementation that uses GiveWP's currency list and filter.
 * Implements the Money library's Currencies interface to ensure Money operations
 * use GiveWP's configured currencies and decimal settings.
 *
 * @since 4.10.0
 */
class GiveCurrencies implements Currencies
{
    /**
     * @since 4.10.0
     */
    public function contains(Currency $currency): bool
    {
        $supportedCurrencies = array_keys(give_get_currencies_list());
        return in_array($currency->getCode(), $supportedCurrencies, true);
    }

    /**
     * @since 4.10.0
     */
    public function subunitFor(Currency $currency): int
    {
        if (!$this->contains($currency)) {
            throw new UnknownCurrencyException('Unknown currency: ' . $currency->getCode());
        }

        $currencies = give_get_currencies_list();
        $currencyData = $currencies[$currency->getCode()] ?? [];
        $decimals = $currencyData['setting']['number_decimals'] ?? 2;

        return (int) $decimals;
    }

    /**
     * Returns an iterator over all supported currencies.
     *
     * Uses yield (Generator) instead of ArrayIterator for better performance and memory efficiency:
     * - Lazy loading: Creates Currency objects only when needed during iteration
     * - Memory efficient: Doesn't load all 100+ Currency objects into memory at once
     * - Dynamic data: Based on give_get_currencies_list() which can change via filters
     * - Better performance: Especially important for large currency lists (100+ currencies)
     *
     * This approach is more suitable than ArrayIterator (used by BitcoinCurrencies/ISOCurrencies)
     * because GiveWP's currency list is dynamic and potentially large, unlike static ISO lists.
     *
     * @since 4.10.0
     */
    public function getIterator(): \Traversable
    {
        $supportedCurrencies = array_keys(give_get_currencies_list());

        foreach ($supportedCurrencies as $currencyCode) {
            yield new Currency($currencyCode);
        }
    }
}
