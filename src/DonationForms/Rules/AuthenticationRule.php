<?php
namespace Give\DonationForms\Rules;

use Closure;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class AuthenticationRule implements ValidationRule
{

    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'authentication';
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
        if (!is_user_logged_in()) {
            $fail(__('Login required.', 'give'));
        }
    }
}
