<?php

namespace Give\Tests\Unit\DonationForms\TestTraits;

use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

/**
 * @since 3.16.2
 */
trait HasValidationRules {
    /**
     * Asserts that a given validation rule passes.
     *
     * @since 3.16.2
     *
     * @param mixed $value
     */
    public static function assertValidationRulePassed(
        ValidationRule $rule,
        $value,
        string $key = '',
        array $values = [],
        bool $shouldPass = true
    ): void {
        $error = null;
        $fail = function ($message) use (&$error) {
            $error = $message;
        };

        $rule($value, $fail, $key, $values);

        if ($shouldPass) {
            self::assertNull($error, 'Validation rule failed. Value: ' . print_r($value, true));
        } else {
            self::assertNotNull($error, 'Validation rule passed. Value: ' . print_r($value, true));
        }
    }

    /**
     * Asserts that a given validation rule fails.
     *
     * @since 3.16.2
     *
     * @param mixed $value
     */
    public static function assertValidationRuleFailed(
        ValidationRule $rule,
        $value,
        string $key = '',
        array $values = []
    ): void {
        self::assertValidationRulePassed($rule, $value, $key, $values, false);
    }
}
