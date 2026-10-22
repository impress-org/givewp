<?php

namespace Give\DonationForms\Rules\Concerns;

use Exception;
use Give\Framework\Support\ValueObjects\Money;

use function is_string;

/**
 * Parses a submitted amount the same way the donation's Money is created from it, so amount rules compare
 * the value that is stored and charged rather than a cast of the raw string.
 *
 * @since TBD
 */
trait ParsesAmountAsMoney
{
    /**
     * @since TBD
     *
     * @return float|null The decimal amount, or null when it cannot be parsed as money in the currency.
     */
    protected function parseAmount(string $amount, string $currency): ?float
    {
        try {
            return (float)Money::fromDecimal($amount, $currency)->formatToDecimal();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * The currency submitted with the amount, or the site currency when none is submitted.
     *
     * @since TBD
     */
    protected function submittedCurrency(array $values): string
    {
        return is_string($values['currency'] ?? null) ? $values['currency'] : give_get_currency();
    }
}
