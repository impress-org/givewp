<?php

namespace Give\Tests\Unit\DonationForms\Rules;

use Give\DonationForms\Rules\Min;
use Give\Tests\TestCase;
use Give\Tests\Unit\DonationForms\TestTraits\HasValidationRules;

/**
 * @since TBD
 * @covers Give\DonationForms\Rules\Min
 */
class TestMinRule extends TestCase
{
    use HasValidationRules;

    /**
     * @since TBD
     */
    public function testFailsWhenAmountIsBelowTheMinimum(): void
    {
        self::assertValidationRuleFailed(new Min(500), 499);
    }

    /**
     * @since TBD
     */
    public function testPassesWhenAmountIsAtOrAboveTheMinimum(): void
    {
        self::assertValidationRulePassed(new Min(500), 500);
        self::assertValidationRulePassed(new Min(500), 501);
    }

    /**
     * @since TBD
     */
    public function testPassesWhenAmountIsExemptEvenThoughItIsBelowTheMinimum(): void
    {
        $rule = (new Min(500))->exemptAmounts(10.0, 25.0, 500.0);

        self::assertValidationRulePassed($rule, 10);
        self::assertValidationRulePassed($rule, '25');
        self::assertValidationRulePassed($rule, 25.00);
    }

    /**
     * @since TBD
     */
    public function testFailsWhenAmountIsBelowTheMinimumAndNotExempt(): void
    {
        $rule = (new Min(500))->exemptAmounts(10.0, 25.0, 500.0);

        self::assertValidationRuleFailed($rule, 11);
        self::assertValidationRuleFailed($rule, 24.99);
    }

    /**
     * @since TBD
     */
    public function testPassesWhenAnExemptAmountIsADecimal(): void
    {
        $rule = (new Min(500))->exemptAmounts(10.5, 25.0);

        self::assertValidationRulePassed($rule, '10.50');
        self::assertValidationRulePassed($rule, 10.5);
        self::assertValidationRuleFailed($rule, '10.51');
    }

    /**
     * @since TBD
     */
    public function testExemptAmountsDefaultToAnEmptyList(): void
    {
        self::assertSame([], (new Min(500))->getExemptAmounts());
    }
}
