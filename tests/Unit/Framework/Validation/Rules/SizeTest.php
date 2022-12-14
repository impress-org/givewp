<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Framework\Validation\Rules;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Validation\Exceptions\ValidationException;
use Give\Framework\Validation\Rules\Size;
use Give\Tests\TestCase;

class SizeTest extends TestCase
{
    /**
     * @dataProvider validationsProvider
     */
    public function testRuleValidations($value, $shouldPass)
    {
        $rule = new Size(3);

        if ( $shouldPass ) {
            self::assertValidationRulePassed($rule, $value);
        } else {
            self::assertValidationRuleFailed($rule, $value);
        }
    }

    public function validationsProvider(): array
    {
        return [
            // numbers
            [3, true],
            [3.0, true],
            [3.1, false],
            [1, false],
            [5, false],

            // strings
            ['bob', true],
            ['bobby', false],
            ['bo', false],
            ['', false],
        ];
    }

    public function testRuleShouldThrowValidationExceptionForInvalidValue()
    {
        $this->expectException(ValidationException::class);

        $rule = new Size(5);
        self::assertValidationRulePassed($rule, true);
    }

    public function testRuleThrowsExceptionForNonPositiveSize()
    {
        $this->expectException(InvalidArgumentException::class);
        new Size(0);
    }
}
