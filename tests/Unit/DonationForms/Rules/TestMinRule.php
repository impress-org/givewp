<?php

namespace Give\Tests\Unit\DonationForms\Rules;

use Give\DonationForms\Rules\Min;
use Give\Tests\TestCase;
use Give\Tests\Unit\DonationForms\TestTraits\HasValidationRules;

/**
 * @since 4.16.8
 * @covers Give\DonationForms\Rules\Min
 */
class TestMinRule extends TestCase
{
    use HasValidationRules;

    /**
     * @since 4.16.8
     */
    public function testFailsWhenAmountIsBelowTheMinimum(): void
    {
        self::assertValidationRuleFailed(new Min(500), 499);
    }

    /**
     * @since 4.16.8
     */
    public function testPassesWhenAmountIsAtOrAboveTheMinimum(): void
    {
        self::assertValidationRulePassed(new Min(500), 500);
        self::assertValidationRulePassed(new Min(500), 501);
    }

    /**
     * @since 4.16.8
     */
    public function testPassesWhenAmountIsExemptEvenThoughItIsBelowTheMinimum(): void
    {
        $rule = (new Min(500))->exemptAmounts(10.0, 25.0, 500.0);

        self::assertValidationRulePassed($rule, 10);
        self::assertValidationRulePassed($rule, '25');
        self::assertValidationRulePassed($rule, 25.00);
    }

    /**
     * @since 4.16.8
     */
    public function testFailsWhenAmountIsBelowTheMinimumAndNotExempt(): void
    {
        $rule = (new Min(500))->exemptAmounts(10.0, 25.0, 500.0);

        self::assertValidationRuleFailed($rule, 11);
        self::assertValidationRuleFailed($rule, 24.99);
    }

    /**
     * @since 4.16.8
     */
    public function testPassesWhenAnExemptAmountIsADecimal(): void
    {
        $rule = (new Min(500))->exemptAmounts(10.5, 25.0);

        self::assertValidationRulePassed($rule, '10.50');
        self::assertValidationRulePassed($rule, 10.5);
        self::assertValidationRuleFailed($rule, '10.51');
    }

    /**
     * @since 4.16.8
     */
    public function testKeepsAFractionalSize(): void
    {
        $rule = new Min('10.50');

        self::assertSame(10.5, $rule->getSize());
        self::assertSame(10.5, $rule->serializeOption());
        self::assertValidationRuleFailed($rule, 10.25);
        self::assertValidationRulePassed($rule, 10.5);
    }

    /**
     * @since 4.16.8
     */
    public function testKeepsAWholeSizeAnInteger(): void
    {
        self::assertSame(500, (new Min('500'))->serializeOption());
    }

    /**
     * @since 4.16.8
     */
    public function testExemptAmountsDefaultToAnEmptyList(): void
    {
        self::assertSame([], (new Min(500))->getExemptAmounts());
    }
}
