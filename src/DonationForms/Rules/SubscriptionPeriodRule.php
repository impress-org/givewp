<?php
namespace Give\DonationForms\Rules;

use Closure;
use Give\Donations\ValueObjects\DonationType;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Vendors\StellarWP\Validation\Contracts\Sanitizer;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class SubscriptionPeriodRule implements ValidationRule, ValidatesOnFrontEnd, Sanitizer
{

    /**
     * @since 3.0.0
     */
    public static function id(): string
    {
        return 'subscriptionPeriod';
    }

    /**
     * @since 4.17.0 Declare the nullable parameter explicitly.
     * @since 3.0.0
     */
    public static function fromString(?string $options = null): ValidationRule
    {
        return new self();
    }

    /**
     * @since TBD Number the placeholders and add translators comments.
     * @since 3.0.0
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        $periods = array_values(SubscriptionPeriod::toArray());
        $donationType = new DonationType($values['donationType']);

        if ($donationType->isSubscription() && !in_array($value, $periods, true)) {
            $fail(
                sprintf(
                    /* translators: 1: Submitted value, 2: Comma-separated list of valid subscription periods */
                    __('%1$s must be a valid subscription period.  Valid periods are: %2$s', 'give'),
                    '{value}',
                    implode(
                        ', ',
                        $periods
                    )
                )
            );
        }
    }

    /**
     * @since 3.0.0
     * @return SubscriptionPeriod|null
     */
    public function sanitize($value)
    {
        if (SubscriptionPeriod::search($value)) {
            return new SubscriptionPeriod($value);
        }
        
        return null;
    }

    /**
     * @since 3.0.0
     */
    public function serializeOption()
    {
        // TODO: Implement serializeOption() method.
    }
}