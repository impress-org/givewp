<?php

namespace Give\DonationForms\Rules;

use Closure;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class ObjectRule implements ValidationRule, ValidatesOnFrontEnd
{
    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'object';
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
        // Although the field is validated as an object in the frontend, it is passed as an array to the backend.
        if (!empty($value) && !is_array($value)) {
            $fail(sprintf(__('%s must be an object', 'give'), '{field}'));
        }
    }

    /**
     * @unreleased
     */
    public function serializeOption()
    {
        return null;
    }
}
