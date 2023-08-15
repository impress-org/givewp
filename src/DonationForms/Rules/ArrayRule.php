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
     * @unreleased
     */
    public static function id(): string
    {
        return 'array';
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
        if (!empty($value) && !is_array($value)) {
            $fail(sprintf(__('%s must be an array', 'give'), '{field}'));
        }
    }

    /**
     * @unreleased
     */
    public function sanitize($value): string
    {
        return $this->sanitizeAsString ? implode(' | ', $value) : $value;
    }

    /**
     * @unreleased
     */
    public function serializeOption()
    {
        return null;
    }
}
