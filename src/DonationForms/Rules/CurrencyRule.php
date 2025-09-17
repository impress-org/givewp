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

        if (!in_array($value, $supportedCurrencies, true)) {
            $fail(
                sprintf(
                    __('%s must be a valid currency. Valid currencies are: %s', 'give'),
                    '{field}',
                    implode(', ', $supportedCurrencies)
                )
            );
        }
    }
}
