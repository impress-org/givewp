<?php

namespace Give\DonationForms\Rules;

use Closure;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @since 3.0.0
 */
class BillingAddressZipRule implements ValidationRule
{
    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'zip';
    }

    /**
     * @since 3.0.0
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @since 3.0.0
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if ( ! $value && ! array_key_exists($values['country'], give_get_country_list_without_postcodes())) {
            $fail(__('Zip required.', 'give'));
        }
    }
}

