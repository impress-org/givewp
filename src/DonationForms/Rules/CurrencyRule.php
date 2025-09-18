<?php

namespace Give\DonationForms\Rules;

use Closure;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * Custom currency validation rule that uses GiveWP's currency list and filter.
 * Replaces the generic stellar validation library currency rule to ensure consistency
 * with GiveWP's configured currencies and filters.
 *
 * @unreleased
 */
class CurrencyRule implements ValidationRule
{
    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'giveCurrency';
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (empty($value)) {
            return;
        }

        // Get GiveWP's supported currencies
        $supportedCurrencies = array_keys(give_get_currencies_list());

        // Check format first for better error messaging
        if (is_string($value) && !$this->isValidFormat($value)) {
            $fail(
                sprintf(
                    __('%s must be a valid 3-letter currency code in uppercase format (e.g., USD). Provided: %s', 'give'),
                    '{field}',
                    $value
                )
            );
        } elseif (!in_array($value, $supportedCurrencies, true)) {
            $providedValue = is_array($value) ? 'array' : (is_object($value) ? 'object' : (string) $value);
            $fail(
                sprintf(
                    __('%s must be a valid currency. Provided: %s. Valid currencies are: %s', 'give'),
                    '{field}',
                    $providedValue,
                    implode(', ', $supportedCurrencies)
                )
            );
        }
    }

    /**
     * Checks if a currency code is in the correct ISO 4217 format.
     * Valid format: exactly 3 uppercase alphabetic characters.
     *
     * @unreleased
     *
     * @param string $value The currency code to validate
     * @return bool True if the format is valid, false otherwise
     */
    private function isValidFormat(string $value): bool
    {
        return strlen($value) === 3 && ctype_alpha($value) && $value === strtoupper($value);
    }
}
