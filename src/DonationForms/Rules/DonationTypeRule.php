<?php
namespace Give\DonationForms\Rules;

use Closure;
use Give\Donations\ValueObjects\DonationType;
use Give\Vendors\StellarWP\Validation\Contracts\Sanitizer;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class DonationTypeRule implements ValidationRule, ValidatesOnFrontEnd, Sanitizer
{

    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'donationType';
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
        $donationTypes = [DonationType::SINGLE()->getValue(), DonationType::SUBSCRIPTION()->getValue()];

        if (!in_array($value, $donationTypes, true)) {
            $fail(
                sprintf(
                    __('%s must be a valid donation type.  Valid types are: %s', 'give'),
                    '{field}',
                    implode(
                        ', ',
                        $donationTypes
                    )
                )
            );
        }
    }

    /**
     * @since 3.0.0
     */
    public function sanitize($value): DonationType
    {
        return new DonationType($value);
    }

    /**
     * @since 3.0.0
     */
    public function serializeOption()
    {
        return null;
    }
}