<?php
namespace Give\DonationForms\Rules;

use Closure;
use Give\Donations\ValueObjects\DonationType;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class SubscriptionInstallmentsRule implements ValidationRule, ValidatesOnFrontEnd
{

    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'subscriptionInstallments';
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