<?php

namespace Give\DonationForms\Rules;

use Closure;
use Give\Helpers\IntlTelInput;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @since 3.9.0
 */
class PhoneIntlInputRule implements ValidationRule
{
    /**
     * @since 3.9.0
     */
    public static function id(): string
    {
        return 'intl-input';
    }

    /**
     * @since 3.9.0
     */
    public static function fromString(string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * It handles the possible error codes returned by the getValidationError() intl function.
     *
     * 0|4 = Invalid number
     * 1 = Invalid country code
     * 2 = Too short
     * 3 = Too long
     *
     * @see https://intl-tel-input.com/examples/validation.html
     *
     * @since 3.9.0
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        $errorMap = IntlTelInput::getErrorMap();

        if ($value && 1 === strlen($value) && array_key_exists(absint($value), $errorMap)) {
            $errorCode = absint($value) ?? 0;
            $fail($errorMap[$errorCode]);
        }
    }
}
