<?php
namespace Give\NextGen\DonationForm\Rules;

use Closure;
use Give\Donations\ValueObjects\DonationType;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class DonationTypeRule implements ValidationRule, ValidatesOnFrontEnd {

    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'donationType';
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
        $donationTypes = [DonationType::SINGLE()->getValue(), DonationType::SUBSCRIPTION()->getValue()];

        if (!in_array($value, $donationTypes, true)) {
            $fail(
                sprintf(__('%s must be a valid donation type.  Valid types are: %s', 'give'),
                '{field}',
                implode(
                    ', ',
                    $donationTypes
                ))
            );
        }
    }

    public function serializeOption()
    {
        return null;
    }
}