<?php
namespace Give\NextGen\DonationForm\Rules;

use Closure;
use Give\Donations\ValueObjects\DonationType;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class SubscriptionFrequencyRule implements ValidationRule, ValidatesOnFrontEnd {

    /**
     * @unreleased
     */
    public static function id(): string
    {
        return 'subscriptionFrequency';
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
    public function __invoke($value, Closure $fail, string $key, array $values): bool
    {
        $donationType = new DonationType($values['donationType']);

        return $donationType->isSubscription() && is_numeric($value);
    }

    public function serializeOption()
    {
        return null;
    }
}