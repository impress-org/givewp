<?php
namespace Give\DonationForms\Rules;

use Closure;
use Give\Vendors\StellarWP\Validation\Contracts\Sanitizer;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class ArrayRule implements ValidationRule, ValidatesOnFrontEnd, Sanitizer
{
    /**
     * @var boolean
     */
    private $sanitizeAsString;

    public function __construct($useSanitizer = false)
    {
        $this->sanitizeAsString = $useSanitizer;
    }

    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'array';
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
        if (!empty($value) && !is_array($value)) {
            $fail(sprintf(__('%s must be an array', 'give'), '{field}'));
        }
    }

    /**
     * @since 3.0.0
     */
    public function sanitize($value): string
    {
        return $this->sanitizeAsString ? implode(' | ', $value) : $value;
    }

    /**
     * @since 3.0.0
     */
    public function serializeOption()
    {
        return null;
    }
}
