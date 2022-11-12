<?php

declare(strict_types=1);

namespace GiveTests\Unit\Framework\Validation\Rules;

use Give\Framework\Validation\Rules\Numeric;
use GiveTests\TestCase;
class NumericTest extends TestCase
{
    /**
     * @unreleased
     */
    public function testShouldPassWhenValueIsNumeric()
    {
        $rule = new Numeric();

        self::assertValidationRulePassed($rule, 1);
        self::assertValidationRulePassed($rule, 100.00);
        self::assertValidationRulePassed($rule, '1');
        self::assertValidationRulePassed($rule, '100.00');
    }

    /**
     * @unreleased
     */
    public function testShouldFailWhenValueIsNotNumeric()
    {
        $rule = new Numeric();

        self::assertValidationRuleFailed($rule, 'abc');
        self::assertValidationRuleFailed($rule, 'abc123');
        self::assertValidationRuleFailed($rule, '123abc');
        self::assertValidationRuleFailed($rule, '123.abc');
        self::assertValidationRuleFailed($rule, '123.345.56');
        self::assertValidationRuleFailed($rule, 'abc.123');
        self::assertValidationRuleFailed($rule, 'abc123.abc');
        self::assertValidationRuleFailed($rule, 'abc.123abc');
        self::assertValidationRuleFailed($rule, 'abc123.abc456');
    }
}
