<?php

namespace Give\Tests\Unit\DonationForms\Rules;

use Give\DonationForms\Rules\Max;
use Give\Tests\TestCase;
use Give\Tests\Unit\DonationForms\TestTraits\HasValidationRules;

/**
 * @since TBD
 * @covers Give\DonationForms\Rules\Max
 */
class TestMaxRule extends TestCase
{
    use HasValidationRules;

    /**
     * @since TBD
     */
    public function testFailsWhenAmountIsAboveTheMaximum(): void
    {
        self::assertValidationRuleFailed(new Max(100), 101);
    }

    /**
     * @since TBD
     */
    public function testPassesWhenAmountIsAtOrBelowTheMaximum(): void
    {
        self::assertValidationRulePassed(new Max(100), 100);
        self::assertValidationRulePassed(new Max(100), 99);
    }

    /**
     * @since TBD
     */
    public function testPassesWhenAmountIsExemptEvenThoughItIsAboveTheMaximum(): void
    {
        $rule = (new Max(100))->exemptAmounts(10.0, 250.0, 500.0);

        self::assertValidationRulePassed($rule, 250);
        self::assertValidationRulePassed($rule, '500');
    }

    /**
     * @since TBD
     */
    public function testFailsWhenAmountIsAboveTheMaximumAndNotExempt(): void
    {
        $rule = (new Max(100))->exemptAmounts(10.0, 250.0, 500.0);

        self::assertValidationRuleFailed($rule, 251);
    }

    /**
     * @since TBD
     */
    public function testReportsExceedingTheMaximum(): void
    {
        self::assertSame('{field} must be less than or equal to 100', $this->_failureMessage(new Max(100), 101));
    }

    /**
     * @since TBD
     */
    public function testReportsExceedingTheMaximumLength(): void
    {
        self::assertSame(
            '{field} must be less than or equal to 3 characters',
            $this->_failureMessage(new Max(3), 'abcd')
        );
    }

    /**
     * Runs the rule against a failing value and returns the message it reported.
     *
     * @since TBD
     *
     * @param mixed $value
     */
    private function _failureMessage(Max $rule, $value): string
    {
        $error = null;
        $fail = static function ($message) use (&$error) {
            $error = $message;
        };

        $rule($value, $fail, 'amount', []);

        return (string)$error;
    }
}
