<?php

namespace Give\DonationForms\Rules;

use Closure;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @unreleased
 */
class BillingAddressZipRule implements ValidationRule
{
    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'zip';
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
        if ( ! $value && ! array_key_exists($values['country'], give_get_country_list_without_postcodes())) {
            $fail(__('Zip required.', 'give'));
        }
    }
}

