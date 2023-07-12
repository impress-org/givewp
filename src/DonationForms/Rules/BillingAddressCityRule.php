<?php

namespace Give\DonationForms\Rules;

use Closure;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @unreleased
 */
class BillingAddressCityRule implements ValidationRule
{
    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'city';
    }

    /**
     * @unreleased
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @unreleased
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if ( ! $value && ! array_key_exists($values['country'], give_city_not_required_country_list())) {
            $fail(__('City required.', 'give'));
        }
    }
}

