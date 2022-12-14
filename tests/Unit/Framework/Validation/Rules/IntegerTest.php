<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Framework\Validation\Rules;

use Give\Framework\Validation\Rules\Integer;
use Give\Tests\TestCase;

class IntegerTest extends TestCase
{
    /**
     * @unreleased
     *
     * @dataProvider integersProvider
     */
    public function testRuleValidatesIntegers($value, $pass)
    {
        $rule = new Integer();

        if ( $pass) {
            self::assertValidationRulePassed($rule, $value);
        } else {
            self::assertValidationRuleFailed($rule, $value);
        }
    }

    /**
     * @unreleased
     */
    public function testCastsToInteger()
    {
        $rule = new Integer();
        self::assertSame(1, $rule->sanitize('1'));
    }

    /**
     * @unreleased
     */
    public function integersProvider(): array
    {
        return [
            [1, true],
            [0, true],
            [-1, true],
            ['12345', true],
            ['-123', true],
            [1.00, true],
            ['04', false],
            [1.32, false],
            ['abc', false],
            [[], false],
            [true, false],
            ['true', false],
        ];
    }
}
