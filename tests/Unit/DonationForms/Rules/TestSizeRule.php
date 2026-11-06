<?php

namespace Give\Tests\Unit\DonationForms\Rules;

use Give\DonationForms\Rules\Size;
use Give\Tests\TestCase;
use Give\Tests\Unit\DonationForms\TestTraits\HasValidationRules;

/**
 * @since 4.19.0.2
 * @covers Give\DonationForms\Rules\Size
 */
class TestSizeRule extends TestCase
{
    use HasValidationRules;

    /**
     * @since 4.19.0.2
     */
    public function testPassesWhenAmountMatchesTheSize(): void
    {
        $rule = new Size(100);

        self::assertValidationRulePassed($rule, 100, 'amount', ['currency' => 'USD']);
        self::assertValidationRulePassed($rule, '100', 'amount', ['currency' => 'USD']);
        self::assertValidationRulePassed($rule, '100.00', 'amount', ['currency' => 'USD']);
        self::assertValidationRulePassed($rule, 100.0, 'amount', ['currency' => 'USD']);
    }

    /**
     * @since 4.19.0.2
     */
    public function testFailsWhenAmountDiffersFromTheSize(): void
    {
        $rule = new Size(100);

        self::assertValidationRuleFailed($rule, 99, 'amount', ['currency' => 'USD']);
        self::assertValidationRuleFailed($rule, '100.01', 'amount', ['currency' => 'USD']);
    }

    /**
     * @since 4.19.0.2
     */
    public function testFailsWhenTheParsedMoneyAmountDiffersFromTheSize(): void
    {
        $rule = new Size(100);

        foreach (['00100', '0100', '0100.0', '1e2', '+100'] as $value) {
            self::assertValidationRuleFailed($rule, $value, 'amount', ['currency' => 'USD']);
        }
    }

    /**
     * @since 4.19.0.2
     */
    public function testKeepsAFractionalSize(): void
    {
        $rule = new Size('10.50');

        self::assertValidationRulePassed($rule, '10.5', 'amount', ['currency' => 'USD']);
        self::assertValidationRuleFailed($rule, '010.50', 'amount', ['currency' => 'USD']);
    }

    /**
     * @since 4.19.0.2
     */
    public function testParsesTheAmountInTheSubmittedCurrency(): void
    {
        $rule = new Size(500);

        self::assertValidationRulePassed($rule, '500', 'amount', ['currency' => 'JPY']);
        self::assertValidationRuleFailed($rule, '0500', 'amount', ['currency' => 'JPY']);
    }

    /**
     * @since 4.19.0.2
     */
    public function testFailsWhenTheCurrencyIsUnknown(): void
    {
        self::assertValidationRuleFailed(new Size(100), '100', 'amount', ['currency' => 'ZZZ']);
    }
}
