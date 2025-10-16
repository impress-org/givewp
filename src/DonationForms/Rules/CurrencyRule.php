<?php

namespace Give\DonationForms\Rules;

use Closure;
use Give\Framework\Support\Currencies\GiveCurrencies;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;
use Money\Currency;

/**
 * Custom currency validation rule that uses GiveWP's currency list and filter.
 * Replaces the generic stellar validation library currency rule to ensure consistency
 * with GiveWP's configured currencies and filters.
 *
 * @since 4.10.0
 */
class CurrencyRule implements ValidationRule
{
    /**
     * @since 4.10.0
     */
    public static function id(): string
    {
        return 'giveCurrency';
    }

    /**
     * @since 4.10.0
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * Validates that the currency code is in GiveWP's supported currency list.
     * Uses give_get_currencies_list() to get the current supported currencies
     * and provides clear error messages with valid currency options.
     *
     * @since 4.10.0
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        // Check format first for better error messaging
        if (!$this->isValidFormat($value)) {
            $fail(
                sprintf(
                    __('%s must be a valid 3-letter currency code in uppercase format (example: USD)', 'give'),
                    '{field}'
                )
            );
        } elseif (!give(GiveCurrencies::class)->contains(new Currency($value))) {
            $fail(
                sprintf(
                    __('%s must be a valid currency. Provided: %s', 'give'),
                    '{field}',
                    $value
                )
            );
        }
    }

    /**
     * Checks if a currency code is in the correct ISO 4217 format.
     * Valid format: exactly 3 uppercase alphabetic characters (not empty).
     *
     * @since 4.10.0
     *
     * @param mixed $value The currency code to validate
     * @return bool True if the format is valid, false otherwise
     */
    private function isValidFormat($value): bool
    {
        return is_string($value) && !empty($value) && strlen($value) === 3 && ctype_alpha($value) && $value === strtoupper($value);
    }
}
