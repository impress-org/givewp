<?php

namespace Give\DonationForms\Rules;

use Closure;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @unreleased
 */
class PhoneIntlInputRule implements ValidationRule
{
    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'intl-input';
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
        $errorMap = [
            __('Invalid number.', 'give'),
            __('Invalid country code.', 'give'),
            __('Invalid number: too short.', 'give'),
            __('Invalid number: too long.', 'give'),
            __('Invalid number.', 'give'),
        ];

        if ($value && 1 === strlen($value) && array_key_exists(absint($value), $errorMap)) {
            $errorCode = absint($value) ?? 0;
            $fail($errorMap[$errorCode]);
        }
    }
}
